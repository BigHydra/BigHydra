<?php
namespace Hydra\BigHydraBundle\Jira\Analyse\Comments;

use Hydra\BigHydraBundle\Jira\Load\MongoRepository;

class Grep
{
    const KEY_PRE_FILTER = 'pre-filter';
    const KEY_UNWIND = 'unwind';
    const KEY_UNWIND_FILTER = 'unwind-filter';
    const KEY_PROJECT = 'project';
    const KEY_GROUP = 'group';
    const KEY_POST_FILTER = 'post-filter';
    const KEY_SORT = 'sort';

    /** @var MongoRepository */
    protected $issueCollection;

    /** @var string[] */
    protected $patternFilter;

    /** @var string[] */
    protected $authorFilter;

    /** @var array[] */
    protected $dateFilter;

    /**
     * @param MongoRepository $issueCollection
     */
    public function __construct(MongoRepository $issueCollection)
    {
        $this->issueCollection = $issueCollection;
        $this->patternFilter = [];
        $this->authorFilter = [];
        $this->dateFilter = [];
    }

    /**
     * @param \string[] $mentionedFilter
     */
    public function setPatternFilter($mentionedFilter)
    {
        $this->patternFilter = $mentionedFilter;
    }

    /**
     * @return \string[]
     */
    public function getPatternFilter()
    {
        return $this->patternFilter;
    }

    /**
     * @param \string[] $author
     */
    public function setAuthorFilter($author)
    {
        $this->authorFilter = $author;
    }

    /**
     * @return \string[]
     */
    public function getAuthorFilter()
    {
        return $this->authorFilter;
    }

    /**
     * @param array[] $dateFilter
     */
    public function setDateFilter($dateFilter)
    {
        $this->dateFilter = $dateFilter;
    }

    /**
     * @return array[]
     */
    public function getDateFilter()
    {
        return $this->dateFilter;
    }

    public function runReport()
    {
        $pipeline = $this->buildAggregationPipeline();
        $pipeline = $this->applyDateFilter($pipeline);
        $pipeline = $this->applyFilterForPattern($pipeline, $this->patternFilter);
        $pipeline = $this->applyFilterForAuthors($pipeline, $this->authorFilter);
        $finalPipeline = $this->cleanupPipeline($pipeline);

        $result = $this->issueCollection->aggregate($finalPipeline);
        return $this->convertAggregationResult($result);
    }

    /**
     * @return array
     */
    protected function buildAggregationPipeline()
    {
        $pipeline = [
            static::KEY_PRE_FILTER => [],
            static::KEY_UNWIND => ['$unwind' => '$fields.comment.comments'],
            static::KEY_UNWIND_FILTER => [],
            static::KEY_PROJECT => [
                '$project' => [
                    '_id' => 0,
                    'issue' => '$key',
                    'issueSummary' => '$fields.summary',
                    'updated' => '$fields.comment.comments.updatedDate',
                    'commentAuthor' => '$fields.comment.comments.author.name',
                    'comment' => '$fields.comment.comments.body',
                ],
            ],
            static::KEY_POST_FILTER => [],
            static::KEY_SORT => [
                '$sort' => ['updated' => 1],
            ],
        ];

        return $pipeline;
    }

    /**
     * @param array $pipeline
     *
     * @return array
     */
    protected function applyDateFilter(array $pipeline)
    {
        if (0 < count($this->dateFilter)) {
            $conditions = [];
            foreach ($this->dateFilter as $date) {
                $dateFrom = new \MongoDate(strtotime($date[0]));
                $dateTo = new \MongoDate(strtotime($date[1]));
                $conditions[] = [
                    '$and' => [
                        [
                            'fields.comment.comments.updatedDate' => [
                                '$gte' => $dateFrom,
                            ]
                        ],
                        [
                            'fields.comment.comments.updatedDate' => [
                                '$lt' => $dateTo,
                            ]
                        ]
                    ]
                ];
            }
            $pipeline = $this->addMatchCondition(
                $pipeline,
                static::KEY_UNWIND_FILTER,
                ['$or' => $conditions]
            );
        }

        return $pipeline;
    }

    /**
     * @param array $pipeline
     * @param array $patterns
     *
     * @return array
     */
    protected function applyFilterForPattern(array $pipeline, array $patterns)
    {
        if (0 < count($patterns)) {
            $sections = [
                static::KEY_PRE_FILTER,
                static::KEY_UNWIND_FILTER,
            ];
            foreach ($sections as $section) {
                $pipeline = $this->applySectionFilterForPattern($pipeline, $section, $patterns);
            }
        }

        return $pipeline;
    }

    /**
     * @param array $pipeline
     * @param string $section
     * @param array $patterns
     *
     * @return array
     */
    protected function applySectionFilterForPattern(array $pipeline, $section, array $patterns)
    {
        $collection = [];
        foreach ($patterns as $pattern) {
            $collection[] =
                [
                    'fields.comment.comments.body' => [
                        '$regex' =>
                        sprintf('%s', $pattern)
                    ]
                ];
        }

        $pipeline = $this->addMatchCondition(
            $pipeline,
            $section,
            [
                '$or' => $collection
            ]
        );

        return $pipeline;
    }
    /**
     * @param array $pipeline
     * @param array $authors
     *
     * @return array
     */
    protected function applyFilterForAuthors(array $pipeline, array $authors)
    {
        if (0 < count($authors)) {
            $sections = [
                static::KEY_PRE_FILTER,
                static::KEY_UNWIND_FILTER,
            ];
            foreach ($sections as $section) {
                $pipeline = $this->applySectionFilterForAuthors($pipeline, $section, $authors);
            }
        }

        return $pipeline;
    }

    /**
     * @param array $pipeline
     * @param string $section
     * @param array $authors
     *
     * @return array
     */
    protected function applySectionFilterForAuthors(array $pipeline, $section, array $authors)
    {
        $collection = [];
        foreach ($authors as $author) {
            $collection[] =
                [
                    'fields.comment.comments.author.name' => [
                        '$eq' => $author
                    ]
                ];
        }

        $pipeline = $this->addMatchCondition(
            $pipeline,
            $section,
            [
                '$or' => $collection
            ]
        );

        return $pipeline;
    }

    /**
     * @param array $pipeline
     * @param string $key
     * @param array $condition
     *
     * @return array
     */
    protected function addMatchCondition(array $pipeline, $key, array $condition)
    {
        if (!isset($pipeline[$key]['$match'])) {
            $pipeline[$key]['$match'] = [];
        }
        $pipeline[$key]['$match']['$and'][] = $condition;

        return $pipeline;
    }

    /**
     * @param $pipeline
     * @return array
     */
    protected function cleanupPipeline($pipeline)
    {
        $pipelineKeys = array_keys($pipeline);
        foreach ($pipelineKeys as $key) {
            if (0 === count($pipeline[$key])) {
                unset($pipeline[$key]);
            }
        }
        $pipeline = array_values($pipeline);
        return $pipeline;
    }

    /**
     * @param $aggregationResult
     * @return mixed
     * @throws \RuntimeException
     */
    protected function convertAggregationResult($aggregationResult)
    {
        if (!isset($aggregationResult['ok']) || 1.0 !== $aggregationResult['ok']) {
            throw new \RuntimeException("Aggregation failed!");
        }
        $rawReport = $aggregationResult['result'];

        $result = [];
        foreach ($rawReport as $rawEntry) {
            $entry = $rawEntry;
            $entry = $this->convertMongoDate($entry);

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function convertMongoDate(array $values)
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = $this->convertMongoDate($value);
            } elseif ($value instanceof \MongoDate) {
                $value = date('Y-m-d H:i:s', $value->sec);
            }
            $result[$key] = $value;
        }

        return $result;
    }
}

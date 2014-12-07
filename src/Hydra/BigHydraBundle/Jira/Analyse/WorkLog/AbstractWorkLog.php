<?php
namespace Hydra\BigHydraBundle\Jira\Analyse\WorkLog;

use Hydra\BigHydraBundle\Jira\Load\MongoRepository;

abstract class AbstractWorkLog
{
    const KEY_PRE_FILTER = 'pre-filter';
    const KEY_UNWIND = 'unwind';
    const KEY_UNWIND_FILTER = 'unwind-filter';
    const KEY_GROUP = 'group';
    const KEY_POST_FILTER = 'post-filter';
    const KEY_SORT = 'sort';

    /** @var MongoRepository */
    protected $issueCollection;

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
        $this->authorFilter = [];
        $this->dateFilter = [];
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
        $pipeline = $this->applyFilterForAuthors($pipeline, $this->authorFilter);

//        echo json_encode($pipeline, JSON_PRETTY_PRINT);
//        exit;

        $result = $this->issueCollection->aggregate($this->cleanupPipeline($pipeline));
        return $this->convertAggregationResult($result);
    }

    /**
     * @return array
     */
    protected function buildAggregationPipeline()
    {
        $pipeline = [
            static::KEY_PRE_FILTER => [
                '$match' => [
                    'fields.worklog.total' => ['$gt' => 0],
                ]
            ],
            static::KEY_UNWIND => ['$unwind' => '$fields.worklog.worklogs'],
            static::KEY_UNWIND_FILTER => [],
            static::KEY_GROUP => [
                '$group' => [
                    '_id' => [
                        'author' => '$fields.worklog.worklogs.author.name',
                    ],
                    'time' => ['$sum' => '$fields.worklog.worklogs.timeSpentSeconds'],
                ],
            ],
            static::KEY_POST_FILTER => [],
            static::KEY_SORT => [
                '$sort' => ['_id.author' => 1],
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
                            'fields.worklog.worklogs.startedDate' => [
                                '$gte' => $dateFrom,
                            ]
                        ],
                        [
                            'fields.worklog.worklogs.startedDate' => [
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
                    'fields.worklog.worklogs.author.name' => [
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
            $entry = $rawEntry['_id'];
            unset($rawEntry['_id']);
            $entry = array_merge($entry, $rawEntry);
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

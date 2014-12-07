<?php
namespace Hydra\BigHydraBundle\Jira\Load;

use Hydra\BigHydraBundle\Jira\Transform\DateConverter;

class MongoRepository
{
    /** @var \MongoCollection */
    protected $collection;
    /** @var DateConverter */
    protected $dateConverter;

    /**
     * @param \MongoCollection $collection
     */
    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
        $this->dateConverter = new DateConverter(
            [
                'updated',
                'created',
                'started',
            ]
        );
    }

    /**
     * @param $issues
     */
    public function saveToMongoDb($issues)
    {
        foreach ($issues as $issue) {
            $issue['_id'] = $issue['key'];
            $this->dateConverter->convertRecursive($issue);
            $this->collection->save($issue);
        }
    }

    /**
     * @param string $authorName
     *
     * @return \MongoCursor
     */
    public function readFromMongoDb($authorName)
    {
        $filter = [
            "fields.comment.comments.author.name" => $authorName
        ];

        return $this->collection->find($filter);
    }

    /**
     * @param array $pipeline
     *
     * @return array
     */
    public function aggregate(array $pipeline)
    {
        return $this->collection->aggregate($pipeline);
    }
}

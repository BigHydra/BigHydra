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
     * @param array $issues
     */
    public function save(array $issues)
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
    public function findByAuthor($authorName)
    {
        $filter = [
            "fields.comment.comments.author.name" => $authorName
        ];

        return $this->collection->find($filter);
    }

    /**
     * @return null|string Y-m-d H:i
     */
    public function findLastUpdatedDate()
    {
        $filter = [];
        $fields = ['fields.updatedDate' => 1];
        $sort = ['fields.updatedDate' => -1];

        $lastDocument = $this->collection->find($filter, $fields)->sort($sort)->limit(1)->getNext();
        if (null !== $lastDocument) {
            /** @var \MongoDate $updatedDate */
            $updatedDate = $lastDocument['fields']['updatedDate'];

            return date('Y-m-d H:i', $updatedDate->sec);
        }

        return null;
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

<?php
namespace Hydra\BigHydraBundle\Jira\Analyse\WorkLog;

class ByAuthorAndTicket extends AbstractWorkLog
{
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
                        'issue' => '$key',
                    ],
                    'affectedDates' => [
                        '$addToSet' => '$fields.worklog.worklogs.startedDate',
                    ],
                    'time' => ['$sum' => '$fields.worklog.worklogs.timeSpentSeconds'],
                ],
            ],
            static::KEY_POST_FILTER => [],
            static::KEY_SORT => [
                '$sort' => ['_id.author' => 1, '_id.issue' => 1],
            ],
        ];

        return $pipeline;
    }
}

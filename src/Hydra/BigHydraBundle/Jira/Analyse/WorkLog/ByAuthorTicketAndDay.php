<?php
namespace Hydra\BigHydraBundle\Jira\Analyse\WorkLog;

class ByAuthorTicketAndDay extends AbstractWorkLog
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
                        'summary' => '$fields.summary',
                        'year' => ['$year' => '$fields.worklog.worklogs.startedDate'],
                        'month' => ['$month' => '$fields.worklog.worklogs.startedDate'],
                        'day' => ['$dayOfMonth' => '$fields.worklog.worklogs.startedDate'],
                    ],
                    'details' => [
                        '$push' => [
                            'comment' => '$fields.worklog.worklogs.comment',
                            'startedDate' => '$fields.worklog.worklogs.startedDate',
                            'timeSpentSeconds' => '$fields.worklog.worklogs.timeSpentSeconds',
                        ]
                    ],
                    'time' => ['$sum' => '$fields.worklog.worklogs.timeSpentSeconds'],
                ],
            ],
            static::KEY_POST_FILTER => [],
            static::KEY_SORT => [
                '$sort' => ['_id.author' => 1, '_id.issue' => 1, '_id.year' => 1, '_id.month' => 1, '_id.day' => 1],
            ],
        ];

        return $pipeline;
    }
}

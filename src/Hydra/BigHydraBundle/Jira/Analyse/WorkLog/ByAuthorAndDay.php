<?php
namespace Hydra\BigHydraBundle\Jira\Analyse\WorkLog;

class ByAuthorAndDay extends AbstractWorkLog
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
                        'authorEmail' => [
                            '$ifNull' => [
                                '$fields.worklog.worklogs.author.emailAddress',
                                null,
                            ],
                        ],
//                        'date' => [
//                                '$concat' => [
//                                    '$year' => '$fields.worklog.worklogs.startedDate',
//                                    '-',
//                                    '$month' => '$fields.worklog.worklogs.startedDate',
//                                    '-',
//                                    '$dayOfMonth' => '$fields.worklog.worklogs.startedDate',
//                                ],
//                            ],
                        'year' => ['$year' => '$fields.worklog.worklogs.startedDate'],
                        'month' => ['$month' => '$fields.worklog.worklogs.startedDate'],
                        'day' => ['$dayOfMonth' => '$fields.worklog.worklogs.startedDate'],
                    ],
                    'affectedIssues' => [
                        '$addToSet' => [
                            'issue' => '$key',
                            'summary' => '$fields.summary',
                        ],
                    ],
                    'time' => ['$sum' => '$fields.worklog.worklogs.timeSpentSeconds'],
                ],
            ],
            static::KEY_POST_FILTER => [],
            static::KEY_SORT => [
                '$sort' => ['_id.author' => 1, '_id.year' => 1, '_id.month' => 1, '_id.day' => 1],
            ],
        ];

        return $pipeline;
    }
}

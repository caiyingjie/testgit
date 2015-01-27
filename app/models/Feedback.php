<?php

use EUS\TFeedbackQuery;

class Feedback extends Model
{
    protected $service = 'eus';

    public static $defaultQueryMethod = 'queryFeedback';

    protected $visible = array(
        'user_id', 'username', 'created_at', 'content', 'is_processed', 'type', 'feedback', 'feedback_replies', 'replies'
    );

    protected $mutators = array('created_at');

    public static function queryFeedback($userId, $limit = 100, $offset = 0)
    {
        $queryArray = self::getCommentQueryArray($userId, $offset, $limit);
        $queryArray['limit'] = $limit;
        $queryArray['offset'] = $offset;echo 4567;
        $feedbacks = self::factory()->call('query_feedback_with_replies')->with(new TFeedbackQuery($queryArray))->query();
        $feedbacksToFount = array();
        foreach ($feedbacks as $feedback) {
            $feedback->feedback->created_at = date(DATE_ISO8601, $feedback->feedback->created_at);
            foreach ($feedback->feedback_replies as $replies) {
                $replies->created_at = date(DATE_ISO8601, $replies->created_at);
                unset($replies->is_valid);
            }
            $feedback->feedback->replies = $feedback->feedback_replies;
            array_push($feedbacksToFount, array_intersect_key((array)$feedback->feedback, array_flip($feedback->visible)));          
        }ffffff
        return $feedbacksToFount;
    }

    public static function count($userId)
    {
        $queryArray = self::getCommentQueryArray($userId);
        return self::factory()->call('count_feedback')->with(new TFeedbackQuery($queryArray))->result(0);
    }

    public static function processForm($userId, $content, $type, $entry_id = 0, $zone_id = 0, $district_id = 0, $city_id, $geohash)
    {
        return self::factory()->call('add_feedback')->with($userId, $content, $type, $entry_id, $zone_id, $district_id,
         $city_id, $geohash, Request::header('user-agent'))->run();
    }

    public function getCreatedAtAttribute($createdAt)
    {
        return date(DATE_ISO8601, $createdAt);
    }

    private static function getCommentQueryArray($userId)
    {
        $queryArray = array();
        $user = User::get($userId);
        $queryArray['username'] = $user->username;
        return $queryArray;
    }
}

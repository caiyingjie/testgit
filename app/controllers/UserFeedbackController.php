<?php

use Eleme\Validation\ValidationException;

class UserFeedbackController extends UserResourceController
{

    protected static $model = 'Feedback';

    protected $defaultQueryMethod = 'queryFeedback';

    public function count($userId)
    {
        return Response::json(
            array(
                'count' => Feedback::count($userId)
            )
        );
    }

    public function store($userId)
    {
        $rules = array(
            'content' => 'required',
            'type' => 'required|numeric|min:0'
        );
        $validation = Validator::make(Input::json()->all(), $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation->messages());
        }
        $params['city_id'] = 0;
        $params['geohash'] = Input::json('geohash',0);
        if($params['geohash'] != 0){
            $city = City::getCityIdByGeohash($params['geohash']);
            $params['city_id'] = $city->id;
        }
        $params['entry_id'] = 0;
        $params['zone_id'] = 0;
        $params['district_id'] = 0;
        $feedback_id = Feedback::processForm(
            $userId,
            Input::json('content'),
            Input::json('type'),
            $params['entry_id'],
            $params['zone_id'],
            $params['district_id'],
            $params['city_id'],
            $params['geohash']
        );
        return Response::json(array('id' => $feedback_id ), 204);
    }
}

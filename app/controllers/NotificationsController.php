<?php

class NotificationsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /notifications
	 *
	 * @return Response
	 */
	public function index()
	{
	    $notifications = Notification::where('user_id', '=', Auth::user()->id)->orderBy('created_at', 'dsc')->get(['_id', 'short_text', 'data']);
        Event::fire('notification.index', Auth::user()->id);

        return View::make('notifications.index')
            ->with('title', 'Notifications')
            ->with('notifications', $notifications->toJSON());

	}

	/**
	 * Display the specified resource.
	 * GET /notifications/{id}
	 *
	 * @param  string $id
	 * @return Response
	 */
	public function show($id)
	{
		$notification = Notification::where('_id', '=', (string)$id)->where('user_id','=', Auth::user()->id)->firstOrFail();
        //$notification->viewed = true;
        $update = Notification::find($id)->update(['viewed' => true]);
        //dd($notification->toArray());

        //$notification->save();

        /*
        //Notification::where('_id', '=', (string)$id)->where('user_id','=', Auth::user()->id)->update(['viewed' => true]);
        $wheres = ['user_id' => ['$in' => [Auth::user()->id]], '_id' => ['$in' => [$id]]];
        $changeTo = ['$set' => ['viewed' => true]];
        $options = ['multi' => false];
        $sailrDB = \DB::connection('mongodb');
        $notificationsCollection = $sailrDB->selectCollection('notifications');

        $notificationsCollection->update($wheres, $changeTo, $options);
*/
        //Notification::findOrFail($id)->update()
       // $notification->viewed = 1;
        //$notification->save();

        return View::make('notifications.show')
            ->with('title', $notification->short_text)
            ->with('notifications', $notification->toJSON());
	}


	/**
	 * Remove the specified resource from storage.
	 * DELETE /notifications/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $notification = Notification::where('_id', '=', $id)->where('user_id','=', Auth::user()->id)->firstOrFail();
        $notification->viewed = 1;
        $notification->save();
	}

}
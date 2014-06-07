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
	    $notifications = Notification::where('user_id', '=', Auth::user()->id)->get(['_id', 'short_text', 'data']);
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
        return View::make('notifications.show')
            ->with('title', $notification->short_text)
            ->with('notification', $notification->toJSON());
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
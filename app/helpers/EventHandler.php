Event::listen('auth.login', function($user)
{
	//Log something in MongoDB analytics here!
});

Event::listen('user.create', function($user)
{
	Mail::send('emails.welcome', $data, function($message)
	{
		$message->from('hi@sailr.co', 'Sailr');
		$message->subject('Welcome to Sailr, ' . $user->name);
		$message->to($user->email)->cc('founders@sailr.co');
	});
});


Event::listen('item.purchase', function($item, $checkout)
{
	Mail::send('emails.welcome', $data, function($message)
	{
		//change this
		$message->from('hi@sailr.co', 'Sailr');
		$message->subject('Welcome to Sailr, ' . $user->name);
		$message->to($user->email)->cc('founders@sailr.co');
	});
});


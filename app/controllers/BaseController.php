<?php

class BaseController extends Controller
{
    /**
     * @var \User $loggedInUser The currently logged in user to the application.
     */
    protected $loggedInUser;

    public function ____construct() {
        if (Auth::check()) {
            $this->loggedInUser = Auth::user();
        }

    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

}
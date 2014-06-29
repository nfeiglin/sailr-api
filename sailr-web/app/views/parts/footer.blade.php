    <div class="page-footer" id="footer" sailr-footer>
        <div class="container">
            <p class="centered">
                <a href="{{ URL::to('/') }}">Home</a> | <a href="{{ URL::action('UsersController@create') }}">Sign up</a> | <a href="{{ URL::action('SessionController@create') }}">Login</a>
            </p>

            <p class="terms centered">
                <a href="{{ URL::action('termsOfService') }}">Terms of service</a>
                |
                <a href="{{ URL::action('privacyPolicy') }}">Privacy policy</a>
            </p>
        </div>
    </div>



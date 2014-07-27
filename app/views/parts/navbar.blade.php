<nav class="navbar navbar-fixed-top" role="navigation">
    <div class="nav-bg">
        <div class="container nav-content center-logo">

            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>


            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="navbar-collapse">

                <div class="nav navbar-nav navbar-left">
                    @if(Auth::guest())
                   <a href="{{ URL::action('UsersController@create') }}" class="btn btn-blue navbar-btn btn-block btn-long">Sign up</a>
                    @endif

                    @if(Auth::check())
                    <a class="btn navbar-btn unstyled-nav-btn" href="{{ URL::to('/') }}">
                        <span class="glyphicon glyphicon-home"></span> Home</a>

                    <a class="btn navbar-btn unstyled-nav-btn" href="{{ URL::action('UsersController@show',Auth::user()->username) }}">
                        <span class="glyphicon glyphicon-user"></span> Me
                    </a>

                    <a class="btn navbar-btn unstyled-nav-btn" href="{{ URL::action('NotificationsController@index') }}">
                        <span class="glyphicon glyphicon-bell"></span> Notifications
                        @if(isset($unread_notifications_count))
                           @if($unread_notifications_count > 0)
                                <span class="badge">{{ $unread_notifications_count }}</span>
                            @endif
                        @endif

                    </a>
                   @endif
                </div>


                <ul class="nav navbar-nav navbar-right">
                    @if(Auth::guest())
                        <a href="/session/create" class="btn navbar-btn unstyled-nav-btn">Login</a>
                    @endif

                    @if(Auth::check())
                    <li>
                        <form ng-submit="submitSearchForm()">
                            <input class="form-inline form-control navbar-btn" placeholder="Search..." ng-model="searchText">
                        </form>
                    </li>
                    <li>

                            <div>
                                <a class="btn navbar-btn unstyled-nav-btn" href="{{ URL::action('ItemsController@index') }}"><span class="glyphicon glyphicon-tags"></span> Products</a>
                            </div>
                    </li>

                    <li class="dropdown list-unstyled">
                        <button class="btn navbar-btn unstyled-nav-btn" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-cog"></span> Settings</button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ URL::action('UsersController@show',Auth::user()->username) }}">
                                    <span class="text-primary">{{{ Auth::user()->name }}}</span>
                                    <span class="text-muted">{{{ Auth::user()->username }}}</span>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li><a href="{{ URL::action('SettingsController@getAccount') }}">Settings</a></li>
                            <li><a href="{{ URL::action('SessionController@destroy') }}">Logout</a></li>
                        </ul>
                    </li>
                 @endif
                </ul>

            </div>
            <!-- /.navbar-collapse -->


        </div>
        <!-- /.container-fluid -->
    </div>
</nav>


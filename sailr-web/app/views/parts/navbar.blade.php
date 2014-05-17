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
                    <a class="btn btn-primary navbar-btn"
                       href="{{ URL::action('UsersController@show',Auth::user()->username) }}">
                        <span class="glyphicon glyphicon-user"></span> Me</a>

                </div>


                <ul class="navbar-right">
                    <li class="dropdown list-unstyled">
                        <a href="#" class="btn btn-primary navbar-btn" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span> Settings</a>
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


                </ul>

            </div>
            <!-- /.navbar-collapse -->


        </div>
        <!-- /.container-fluid -->
    </div>
</nav>


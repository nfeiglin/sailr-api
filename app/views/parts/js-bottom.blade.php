<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular-sanitize.min.js"></script>
<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>

<script src="{{ URL::asset('build/js/main.js') }}"></script>
{{-- @if(App::environment('local', 'debug', 'testing'))
{{ \Sailr\TestPipe\TestPipe::make()->tags('js') }}
{{ @else }}
@endif
--}}

<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js" async="async" defer="defer"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js" async="async" defer="defer"></script>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script defer="defer" async="async">
    Stripe.setPublishableKey('{{ User::getStripePublishableKey() }}');
</script>


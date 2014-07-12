<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.19/angular.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.19/angular-sanitize.min.js" defer="defer"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/jquery.slick/1.3.6/slick.min.js" async="async"></script>
<script src="{{ URL::asset('build/js/main.js') }}"></script>

<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js" async="async" defer="defer"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/humane-js/3.0.6/humane.min.js" async="async" defer="defer"></script>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script async="async">
    Stripe.setPublishableKey('{{ User::getStripePublishableKey() }}');
</script>

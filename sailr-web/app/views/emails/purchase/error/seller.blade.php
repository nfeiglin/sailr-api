@extends('emails.layout.main')
@section('content')

<table class="body">
    <tr>
        <td class="center" align="center" valign="top">
            <center>

                <table class="row header">
                    <tr>
                        <td class="center" align="center">
                            <center>


                                        </td>
                                    </tr>
                                </table>

                            </center>
                        </td>
                    </tr>
                </table>

                <table class="container">
                    <tr>
                        <td>

                            <table class="row">
                                <tr>
                                    <td class="wrapper last">

                                        <table class="twelve columns">
                                            <tr>
                                                <td>
                                                    <h1>Oops. There's been an issue with a sale.</h1>
                                                    <p class="lead">Hi {{ $user->name or 'NAME' }}, <a href="{{ URL::action('UsersController@show', $buyer->username) }}">{{{ $buyer->name }}} (<span>@</span>{{{ $buyer->username }}})</a> just tried to purchase <a href="{{{ URL::action('BuyController@create', $product->id) }}}">{{{ product->title or 'PRODUCT TITLE' }}}</a> but an issue with PayPal occurred.</p>
                                                </td>

                                                <td class="expander"></td>
                                                <td class="twelve columns">
                                                    <td class="panel">
                                                    <h2>Here's some more information on the issue</h2>
                                                    <p class="error-message"> {{ $errorReason or 'THE ERROR MESSAGE GOES HERE!' }}</p>
                                                    </td>
                                                </td>

                                                <td>
                                                    <p>To get in touch with the buyer, <b>{{{ $buyer->name or 'BUYER NAME' }}}</b> (<span class="at-sign-required-blade">@</span>{{ $buyer->username or 'USERNAME' }}) contact them at <a href="mailto:{{ $buyer->email or 'BUYER EMAIL'}}">{{ $buyer->email or 'BUYER@EMAIL.COM'}}</a></p>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            @if($ipn->getBuyersNote() && strlen($ipn->getBuyersNote() > 0))
                            <table class="row callout">
                                <tr>
                                    <td class="wrapper last">


                                        <table class="twelve columns">
                                            <tr>
                                                <td>
                                                    <h5>Buyer's message</h5>
                                                </td>
                                                <td class="panel">
                                                    {{{ $ipn->getBuyersNote() }}}
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                            @endif

                            <table class="row footer">
                                <tr>
                                    <td class="wrapper">

                                        <table class="six columns">
                                            <tr>
                                                <td class="left-text-pad">

                                                    <h5>(Once resolved) Ship the product to:</h5>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <p>{{{ $ipn->getShipToName() }}}</p>
                                                                <p>{{{ $ipn->getAddress1() }}}, {{{ $ipn->getCity() }}}</p>
                                                                <p>{{{ $ipn->getState() }}}, {{{ $ipn->getZipCode() }}}</p>
                                                                <p>{{{ $ipn->getCountry() }}}</p>
                                                            </td>
                                                        </tr>
                                                    </table>


                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                    <td class="wrapper last">

                                        <table class="six columns">
                                            <tr>
                                                <td class="last right-text-pad">
                                                    <h5>Payment information (</h5>
                                                    <p>Note: As a PayPal error occurred, you may not have been paid. Check the message above and your PayPal account for more information.</p>
                                                    <p>Shipping price: {{ $ipn->getShippingPrice() }} {{ $ipn->getCurrencyCode() }}</p>
                                                    <p>Paypal transaction fees: {{ $ipn->getPaymentProcessingFees() }} {{ $ipn->getCurrencyCode() }}</p>
                                                    <p><b>Total:</b> {{ $ipn->getNetPaidToSeller() }} {{ $ipn->getCurrencyCode() }}</p>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>


                            <table class="row">
                                <tr>
                                    <td class="wrapper last">

                                        <table class="twelve columns">
                                            <tr>
                                                <td align="center">
                                                    <center>
                                                        <p style="text-align:center;"><a href="http://sailr.co">Visit Sailr</a> | <a href="mailto:support@sailr.co">Email support@sailr.co for help with Sailr</a>
                                                        <p>Sailr checkout identifier: {{ $checkout->id or 'ZZZ999' }}.</p>
                                                        <p>Sent at: {{ Carbon\Carbon::now()->toDayDateTimeString() }} {{ date_default_timezone_get() }}</p>
                                                    </center>
                                                </td>
                                                <td class="expander"></td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!-- container end below -->
                        </td>
                    </tr>
                </table>

            </center>
        </td>
    </tr>
</table>

@stop
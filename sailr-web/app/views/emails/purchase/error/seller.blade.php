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
                                                    <h1>Congratulations {{ $user->name or 'NAME' }}</h1>
                                                    <p class="lead">You've just sold <a href="@if(isset($product)) {{ URL::action('BuyController@create', $product->id) }} @endif"> {{ $product->title or 'PRODUCT TITLE' }}</a></p>
                                                    <p>If you need to get in touch with the buyer,  <b>{{{ $buyer->name or 'BUYER NAME' }}}</b> (<span class="at-sign-required-blade">@</span>{{ $buyer->username or 'USERNAME' }}) contact them at <a href="mailto:{{ $buyer->email or 'BUYER EMAIL'}}">{{ $buyer->email or 'BUYER@EMAIL.COM'}}</a></p>
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

                                                    <h5>Ship the product to:</h5>
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
                                                    <h5>Payment</h5>
                                                    <p>Shipping price: {{ $ipn->getShippingPrice() }} {{ $ipn->getCurrencyCode() }}</p>
                                                    <p>Paypal transaction fees: {{ $ipn->getPaymentProcessingFees() }} {{ $ipn->getCurrencyCode() }}</p>
                                                    <p><b>You are paid:</b> {{ $ipn->getNetPaidToSeller() }} {{ $ipn->getCurrencyCode() }}</p>
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
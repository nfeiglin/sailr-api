<p>Hi there,</p>
<p>Seeing as though you'd asked, I'd let you know about a recent logged event on Sailr.</p>

<h3>Log level:</h3>
<p>{{ $level or '' }}</p>
<h3>Log message</h3>
<p>{{ $logMessage or '' }}</p>

<h3>Log context</h3>
{{ print_r($context, 1) or '== No context provided ==' }}

<hr>
<p>Yours faithfully, the Sailr Mailbot</p>

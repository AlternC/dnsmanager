
<div style="float: right; margin: 30px;">
<p><?=_("The domains you host in your server should have the following DNS servers:"); ?></p>
<table>
<tr><th>Name</th><th>IPv4</th><th>IPv6</th></tr>
<tr><td style="padding-right: 15px; font-family: Courier-New, fixed;">ns1.alternc.net</td><td>91.194.60.82</td><td>2001:67c:288::82</td></tr>
<tr><td style="padding-right: 15px; font-family: Courier-New, fixed;">ns2.alternc.net</td><td>96.126.102.48</td><td></td></tr>
   <tr><td style="padding-right: 15px; font-family: Courier-New, fixed;">ns3.alternc.net</td><td>193.56.58.27</td><td>2001:67c:288::1000:27</td></tr>
</table>
</div>


<p><?=_("<a href=\"https://alternc.org/\">AlternC</a> is a free software that allows you to control your hosting server easily. As such, it knows how to manage domain names."); ?><br />
<?=_("However, many users don't have their own DNS servers, or not enough of them. AlternC team decided to provide anybody with free DNS service for their servers running AlternC.");  ?><br />
<?=_("To use that service, create an account, and add your AlternC's server to your account. <a href=\"http://aide-alternc.org/\">Read the instructions here</a> to know how to automatically synchronize your server's list of domain names with alternc.net"); ?><br />
<?=_("For each server you have, we will point a subdomain of <i>alternc.net</i> to your server's IP address. Thanks to that, your server will have a name on the Internet even if you didn't install any domain inside it yet."); ?></p>
<hr />

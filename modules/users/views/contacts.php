<h2><?php __("Contacts techniques"); ?></h2>

<?php
  echo html_list(_("Personnes autorisés à agir au nom du client (e-mail)"),
		 $contacts['in_email'],
		 _("Aucun contact e-mail n'est autorisé à agir au nom du client."));
?>
<?php
  echo html_list(_("Personnes autorisés à agir au nom du client (téléphone)"),
		 $contacts['in_tel'],
		 _("Aucun contact téléphonique n'est autorisé à agir au nom du client."));
?>
<?php
  echo html_list(_("Personnes à prévenir en cas d'urgence (e-mail)"),
		 $contacts['out_email'],
		 _("Aucun contact à prévenir par e-mail n'est défini."));
?>
<?php
  echo html_list(_("Personnes à prévenir en cas d'urgence (SMS)"),
		 $contacts['out_tel'],
		 _("Aucun contact à prévenir par sms n'est défini."));
?>

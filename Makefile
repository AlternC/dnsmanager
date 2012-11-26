# Remove the languages/default.pot file and launch make to recreate the pot and merge it into po localized files

all: locales


install-locales:
	mkdir -p ./locales/en_US/LC_MESSAGES/ ./locales/fr_FR/LC_MESSAGES/
	touch ./locales/en_US/LC_MESSAGES/default.mo ./locales/fr_FR/LC_MESSAGES/default.mo
	touch ./locales/en_US/LC_MESSAGES/default.po ./locales/fr_FR/LC_MESSAGES/default.po

locales: ./locales/en_US/LC_MESSAGES/default.mo ./locales/fr_FR/LC_MESSAGES/default.mo


./locales/%/LC_MESSAGES/default.mo: ./locales/%/LC_MESSAGES/default.po
	msgfmt $^ -o $@

./locales/%/LC_MESSAGES/default.po: ./locales/default.pot ./locales/%/LC_MESSAGES
	cp $< $@
	msgmerge -v -U $@ $<

./locales/%/LC_MESSAGES:
	mkdir -p $@

pot: ./locales/default.pot


./locales/default.pot: views/*.php libs/*.php public/*.php modules/*/*.php modules/*/views/*.php *.php
	[ -r $@ ] || touch $@
	xgettext --from-code=UTF-8 --force-po -o $@ --keyword=_ --keyword=__  -L PHP  -j `find -not -name '.*' -a \( -name '*.php' -o -name '*.phtml' \)`


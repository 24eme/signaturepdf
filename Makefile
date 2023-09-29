.PHONY: test all

.DEFAULT_GOAL := all

all: update_trad

node_modules/jest/bin/jest.js:
	npm install jest

node_modules/puppeteer:
	npm install puppeteer

test: node_modules/jest/bin/jest.js node_modules/puppeteer
	./node_modules/jest/bin/jest.js

update_trad:
	# Extraction des phrases traductibles...
	@xgettext --from-code=utf-8 --no-location --output=./locale/application.pot *.php templates/*.php templates/components/*.php

	# Mise a jour des fichiers .po...
	@for lang in $$(find locale -mindepth 1 -maxdepth 1 -type d); do \
    	po_file="$$lang/LC_MESSAGES/application.po"; \
		msgmerge --update -N "$$po_file" ./locale/application.pot; \
	done

	# Creation des fichiers .mo...
	@for lang in $$(find locale -mindepth 1 -maxdepth 1 -type d); do \
        po_file="$$lang/LC_MESSAGES/application.po"; \
        rm -f "$$lang/LC_MESSAGES/application.mo"; \
        msgfmt "$$po_file" --output-file="$$lang/LC_MESSAGES/application.mo"; \
		git add "$$lang/LC_MESSAGES/application.mo"; \
    done

	# Génération des empreintes de .mo..

	@for lang in $$(find locale -mindepth 1 -maxdepth 1 -type d); do \
		checksum="$$(md5sum locale/*/LC_MESSAGES/application.mo | md5sum | cut -d " " -f 1)"; \
		rm $$lang/LC_MESSAGES/application_*.mo; \
		git rm $$lang/LC_MESSAGES/application_*.mo; \
		rm locale/application_*.pot; \
		git rm locale/application_*.pot; \
		ln -s "application.mo" "$$lang/LC_MESSAGES/application_$$checksum.mo"; \
		ln -s "application.pot" "locale/application_$$checksum.pot"; \
		git add "$$lang/LC_MESSAGES/application_$$checksum.mo"; \
		git add "locale/application_$$checksum.pot"; \
	done

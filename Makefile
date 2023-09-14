.PHONY: test all

.DEFAULT_GOAL := all

all: update

node_modules/jest/bin/jest.js:
	npm install jest

node_modules/puppeteer:
	npm install puppeteer

test: node_modules/jest/bin/jest.js node_modules/puppeteer
	./node_modules/jest/bin/jest.js

update:
	# Extraction des phrases traductibles...
	@xgettext --from-code=utf-8 --output=./locale/application.pot templates/*.php

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
        done

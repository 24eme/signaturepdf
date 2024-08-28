.PHONY: test all

.DEFAULT_GOAL := all

VENDOR=24eme
PROJECT=signaturepdf
MAINTAINER=Équipe 24ème <equipe@24eme.fr>
VERSION=$(shell git describe --abbrev=0)
RELEASE=$(shell git describe | cut -d- -f2)
PKGNAME=php-${PROJECT}
DATADIR=usr/share
LIBPATH=$(DATADIR)/$(PKGNAME)/
CONFIGPATH=etc/$(PKGNAME)/

CURRENTDIR=$(dir $(realpath $(firstword $(MAKEFILE_LIST))))
TARGETDIR=$(CURRENTDIR)target
PATHDEBPKG=$(TARGETDIR)/DEB

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

# Build a DEB package for Debian-like Linux distributions
.PHONY: deb
deb:
	rm -rf $(TARGETDIR)
	git clone . $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(LIBPATH)
	rm -rf $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(LIBPATH).git
	rm -rf $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(LIBPATH).debian
	tar -zcvf $(PATHDEBPKG)/$(PKGNAME)_$(VERSION).orig.tar.gz -C $(PATHDEBPKG)/ $(PKGNAME)-$(VERSION)
	rsync -av --exclude=*~ ./.debian/ $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian
	mkdir -p $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(CONFIGPATH)
	mv $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/apache.conf $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/$(CONFIGPATH)
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#DATE#~/`date -R`/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#VENDOR#~/$(VENDOR)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#PROJECT#~/$(PROJECT)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#MAINTAINER#~/$(MAINTAINER)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/ -type f -exec sed -i "s/~#PKGNAME#~/$(PKGNAME)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#VERSION#~/$(VERSION)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/ -type f -exec sed -i "s/~#RELEASE#~/$(RELEASE)/" {} \;
	find $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/ -type f -exec sed -i "s|~#LIBPATH#~|$(LIBPATH)|" {} \;
	echo $(LIBPATH) > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(LIBPATH)* $(LIBPATH)" > $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
ifneq ($(strip $(CONFIGPATH)),)
	echo $(CONFIGPATH) >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/$(PKGNAME).dirs
	echo "$(CONFIGPATH)* $(CONFIGPATH)" >> $(PATHDEBPKG)/$(PKGNAME)-$(VERSION)/debian/install
endif
	cd $(PATHDEBPKG)/$(PKGNAME)-$(VERSION) && debuild -us -uc

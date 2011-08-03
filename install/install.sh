#!/bin/bash

# let's start in the moodle root
cd ../../..

#exclude self
echo /local/moodleorg/ >> .git/info/exclude


# create symbolic link to htaccess with rewrite rules (or hardlink if necessary)
ln -s local/moodleorg/top.htaccess .htaccess
echo /.htaccess >> .git/info/exclude



# blocks  =============================
cd blocks

git clone git@github.com:moodlehq/moodle-block_cool_sites.git cool_sites
cd cool_sites
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /blocks/cool_sites/ >> ../../.git/info/exclude
cd ..

git clone git@github.com:moodlehq/moodle-block_partners.git partners
cd partners
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /blocks/partners/ >> ../../.git/info/exclude
cd ..

git clone git://github.com/moodlehq/moodle-block_unanswered_discussions.git unanswered_discussions
cd unanswered_discussions
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /blocks/unanswered_discussions/ >> ../../.git/info/exclude
cd ..

# back to moodle root
cd ..



# filters =============================
cd filter

git clone git://github.com/moodlehq/moodle-filter_geshi.git geshi
cd geshi
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /filter/geshi/ >> ../../.git/info/exclude
cd ..

git clone git://github.com/moodlehq/moodle-filter_moodledocs.git moodledocs
cd moodledocs
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /filter/moodledocs/ >> ../../.git/info/exclude
cd ..

git clone git://github.com/moodlehq/moodle-filter_moodlelinks.git moodlelinks
cd moodlelinks
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /filter/moodlelinks/ >> ../../.git/info/exclude
cd ..

git clone git://github.com/moodlehq/moodle-filter_skypeicons.git skypeicons
cd skypeicons
git checkout -b MOODLE_21_STABLE origin/MOODLE_21_STABLE
echo /filter/skypeicons/ >> ../../.git/info/exclude
cd ..

# back to moodle root
cd ..


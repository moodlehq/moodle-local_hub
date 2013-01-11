#!/bin/bash

basedir="/var/www/vhosts/moodle.org/html"
gitconfig="git config core.filemode false"

# local-moodleorg ====================
echo -e "\n=============== local/moodleorg"
cd $basedir/local/moodleorg && $gitconfig && git status && git diff

# blocks =============================
echo -e "\n=============== blocks/cool_sites"
cd $basedir/blocks/cool_sites && $gitconfig && git status && git diff
echo -e "\n=============== blocks/partners"
cd $basedir/blocks/partners && $gitconfig  && git status && git diff
echo -e "\n=============== blocks/unanswered_discussions"
cd $basedir/blocks/unanswered_discussions && $gitconfig  && git status && git diff

# filters =============================
echo -e "\n=============== filter/geshi"
cd $basedir/filter/geshi && $gitconfig  && git status && git diff
echo -e "\n=============== filter/moodledocs"
cd $basedir/filter/moodledocs && $gitconfig && git status && git diff
echo -e "\n=============== filter/moodlelinks"
cd $basedir/filter/moodlelinks && $gitconfig && git status && git diff
echo -e "\n=============== filter/skypeicons"
cd $basedir/filter/skypeicons && $gitconfig && git status && git diff

# contrib plugins =====================
echo -e "\n=============== local/plugins"
cd $basedir/local/plugins && $gitconfig && git status && git diff

# theme ===============================
echo -e "\n=============== theme/moodleofficial"
cd $basedir/theme/moodleofficial && $gitconfig && git status && git diff

# moodle ==============================
echo -e "\n=============== moodle core"
cd $basedir && $gitconfig && git status && git diff

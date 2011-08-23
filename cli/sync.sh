#!/bin/bash

basedir="/var/www/vhosts/moodle.org/html"
gitconfig="git config core.filemode false"

# blocks =============================
cd $basedir/blocks/cool_sites && $gitconfig && git reset --hard
cd $basedir/blocks/partners && $gitconfig  && git reset --hard
cd $basedir/blocks/unanswered_discussions && $gitconfig  && git reset --hard

# filters =============================
cd $basedir/filter/geshi && $gitconfig  && git reset --hard
cd $basedir/filter/moodledocs && $gitconfig && git reset --hard
cd $basedir/filter/moodlelinks && $gitconfig && git reset --hard
cd $basedir/filter/skypeicons && $gitconfig && git reset --hard

# contrib plugins =====================
cd $basedir/local/plugins && $gitconfig && git reset --hard

# phpmyadmin ==========================
cd $basedir/local/phpmyadmin && $gitconfig && git reset --hard

# theme ===============================
cd $basedir/theme/moodleofficial && $gitconfig && git reset --hard

# moodle ==============================
cd $basedir && $gitconfig && git reset --hard

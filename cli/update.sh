#!/bin/bash

basedir="/var/www/vhosts/moodle.org/html"
gitconfig="git config core.filemode false"

# blocks =============================
cd $basedir/blocks/cool_sites && $gitconfig && git pull
cd $basedir/blocks/partners && $gitconfig  && git pull
cd $basedir/blocks/unanswered_discussions && $gitconfig  && git pull
cd $basedir/blocks/spam_deletion && $gitconfig  && git pull

# filters =============================
cd $basedir/filter/geshi && $gitconfig  && git pull
cd $basedir/filter/moodledocs && $gitconfig && git pull
cd $basedir/filter/moodlelinks && $gitconfig && git pull
cd $basedir/filter/skypeicons && $gitconfig && git pull

# contrib plugins =====================
cd $basedir/local/plugins && $gitconfig && git pull

# phpmyadmin ==========================
cd $basedir/local/phpmyadmin && $gitconfig && git pull

# dev plugin ==========================
cd $basedir/local/dev && $gitconfig && git pull

# chatlogs plugin =====================
cd $basedir/local/chatlogs && $gitconfig && git pull

# theme ===============================
cd $basedir/theme/moodleofficial && $gitconfig && git pull

# moodle ==============================
cd $basedir && $gitconfig && git pull

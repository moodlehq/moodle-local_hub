#!/bin/bash

# let's start in the moodle root
cd ../../..

# blocks =============================
cd blocks

cd cool_sites
git pull
cd ..

cd partners
git pull
cd ..

cd unanswered_discussions
git pull
cd ..

# back to moodle root
cd ..

# filters =============================
cd filter

cd geshi
git pull
cd ..

cd moodledocs
git pull
cd ..

cd moodlelinks
git pull
cd ..

cd skypeicons
git pull
cd ..

# back to moodle root
cd ..

#contrib plugns
cd local/skypeicons
git pull
cd ../..

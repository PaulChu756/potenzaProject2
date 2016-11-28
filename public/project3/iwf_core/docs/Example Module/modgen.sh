#!/bin/bash

if [ -z "$1" ] || [ -z "$2" ]
then
     echo Usage: $0 singular plural
     exit 0
fi

SINGULAR_LOWER=${1,,}
SINGULAR_UPPER=${1^}

PLURAL_LOWER=${2,,}
PLURAL_UPPER=${2^}

rm $SINGULAR_LOWER -rf

mkdir $SINGULAR_LOWER

rsync -avzC example/ $SINGULAR_LOWER

RENAME_STRING='s/Example/'$SINGULAR_UPPER'/'

#run twice because renaming the higher level makes the lower level unfindable
find $SINGULAR_LOWER -exec rename $RENAME_STRING '{}' \;
find $SINGULAR_LOWER -exec rename $RENAME_STRING '{}' \;

grep -rl 'examples' $SINGULAR_LOWER | xargs sed -i s@examples@$PLURAL_LOWER@g
grep -rl 'example' $SINGULAR_LOWER | xargs sed -i s@example@$SINGULAR_LOWER@g
grep -rl 'Examples' $SINGULAR_LOWER | xargs sed -i s@Examples@$PLURAL_UPPER@g
grep -rl 'Example' $SINGULAR_LOWER | xargs sed -i s@Example@$SINGULAR_UPPER@g

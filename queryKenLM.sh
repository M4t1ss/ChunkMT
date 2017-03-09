# Usage:
# ./exp.sh languagemodel.binary "This is a sentance in English ."

LM=$1
DATA="$2"


echo $DATA | sed -e '$a\' | ./included/query $LM | tail -n 10 | egrep "^(Perplexity including OOVs)" | sed -e 's/Perplexity including OOVs:	//g'


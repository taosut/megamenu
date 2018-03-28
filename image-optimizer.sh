#!/bin/bash

# Install packages command: sudo apt-get install imagemagick php-imagick
# Change 'php-imagick' to 'php5-imagick' for PHP5

# Define color
red='\033[0;31m'
green='\033[0;32m'
yellow='\033[1;33m'
nc='\033[0m'

echo -e "${yellow}\u25FD${nc} Scanning for JPG and PNG images..."

backupFolderName='./media/backup-images/'

findImages=`find ./media/ -type f -not -path "$backupFolderName*" -not -path '*/\.*' -regex '.*\(jpe?g\|png\)'`

count=`find ./media/ -type f -not -path "$backupFolderName*" -not -path '*/\.*' -regex '.*\(jpe?g\|png\)' | wc -l`
echo -e "${yellow}\u25FD${nc} Found $count JPG and PNG files"

# echo -ne "\u2754 Continue to optimize images? (y/n): "
# read choice
#
# if [[ $choice =~ (n|N|no)$ ]]; then
#     echo -e "${red}\u2717 Stop the script!!!"
#     exit
# fi

echo
echo -e "${yellow}\u25FD${nc} Optimizing scanned images..."
echo

mkdir -p $backupFolderName

idx=0
skipCnt=0
jpgCnt=0
pngCnt=0
corruptCnt=0
page=$(( $count / 100 + 1))
startTime=$SECONDS

# Loop over file in media folder
for filename in $findImages; do
    (( idx++ ))
    if (( idx % $page == 0 )); then
        elapsedTime=$(( $SECONDS - $startTime ))
        echo -e "${yellow}\u27A2${nc} Process $idx/$count images ${yellow}\u27A2${nc} Time spent: $(( $elapsedTime/60 )) min $(( $elapsedTime%60 )) sec"
    fi

    if [ -e "$backupFolderName$filename" ]; then
        (( skipCnt++ ))
        continue
    fi

    identify $filename &> /dev/null
    if [ ! $? -eq 0 ]; then
        echo -e "${red}\u2717 File: $filename is corrupted!!${nc}"
        (( corruptCnt++ ))
        continue
    fi

    cp --parents $filename $backupFolderName
    if [[ $filename =~ .*\.jpe?g ]]; then
        convert $filename -sampling-factor 4:2:0 -strip -quality 85 -interlace JPEG -colorspace RGB $filename &> /dev/null
        if [ ! $? -eq 0 ]; then
            cp "$backupFolderName$filename" $filename
            rm "$backupFolderName$filename"
            echo -e "${red}\u2717 File: $filename is incomplete!!${nc}"
            (( corruptCnt++ ))
            continue
        fi
        (( jpgCnt++ ))
    else
        convert $filename -strip $filename &> /dev/null
        if [ ! $? -eq 0 ]; then
            cp "$backupFolderName$filename" $filename
            rm "$backupFolderName$filename"
            echo -e "${red}\u2717 File: $filename is incomplete!!${nc}"
            (( corruptCnt++ ))
            continue
        fi
        (( pngCnt++ ))
    fi
done

# Information
echo
echo -e "${green}\u2713 Done!"
echo -e "\u27A4 $skipCnt files were skipped due to optimized (in backup folder)"
echo -e "\u27A4 $jpgCnt JPG files were optimized"
echo -e "\u27A4 $pngCnt PNG files were optimized"
echo -e "\u27A4 $corruptCnt files are corrupted or incomplete"
elapsedTime=$(( $SECONDS - $startTime ))
echo -e "\u27A4 Total time spent: $(( $elapsedTime/60 )) min $(( $elapsedTime%60 )) sec"

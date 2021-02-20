
function entryCoverSelectChange() {
    let selectCover = document.getElementById('entryCoverSelect');
    const selectCoverValue = selectCover.value;
    selectCover.setAttribute('name', 'flags[entryCover(' + selectCoverValue + ')]');
    entryCoverChangeImage(selectCoverValue);
}

function entryCoverChangeImage(imageName) {
    let coverImage = document.getElementById('entryCoverImage');
    console.log(coverImage.childNodes);
    if(coverImage.childNodes.length > 0) {
        coverImage.removeChild(coverImage.childNodes[0]);
    }
    if(imageName != entryCoverNoCoverOption) {
        let newCoverImage = document.createElement('img');
        newCoverImage.style.width = '100%';
        newCoverImage.style.height = 'auto';
        newCoverImage.src = entryCoverImagesPath + imageName;
        coverImage.appendChild(newCoverImage);
    }
}
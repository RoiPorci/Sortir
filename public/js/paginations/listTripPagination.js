window.addEventListener('load', init);

function init(e){
    let filter = window.location.search;
    let pageLinks = document.querySelectorAll('.page-link');

    for(let pageLink of pageLinks){
        let path = pageLink.href;
        let url = path + filter;
        pageLink.setAttribute('href', url);
    }
}


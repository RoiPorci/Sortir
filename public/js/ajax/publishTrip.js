window.addEventListener('load', init);

function init(event){
    let btnsPublish = document.querySelectorAll('.btn-publish');

    btnsPublish.forEach( (btnPublish) => {
        btnPublish.addEventListener('click', publishTrip);

    });
}

function publishTrip(event){
    btnPublish = event.currentTarget;
    tripId = btnPublish.dataset.id;
    spanState = document.getElementById('trip-state-wording-'+tripId);

    url = "api/trip/publish/"+tripId;

    fetch(url, {method: 'POST'})
        .then((response) => {
            return response.json();
        })
        .then( function(data){
            if(data.isPublished){
                //TODO créer une fenêtre modale pour indiquer que la sortie a été publiée
                spanState.innerText = 'Ouverte';
                btnPublish.remove();
            }
        })
}
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
        .then( (data) => {
            if(data.isPublished){
                spanState.innerText = 'Ouverte';
                btnPublish.remove();

                //Paramétrer la fenêtre la modale
                sendInfoModal('Succès', 'Votre sortie a bien été publiée!', 'success');
            }
            else {
                sendInfoModal('Echec', "Votre sortie n'a pas été publiée!", 'danger');
            }
        })
        .catch( () => {
            sendInfoModal('Echec', "Une erreur avec le serveur est survenue!", 'danger');
        })
}
window.addEventListener('load', init);

function init(event){
    let btnsUpdate = document.querySelectorAll('.btn-update-participant');

    btnsUpdate.forEach( (btnUpdate) => {
        btnUpdate.addEventListener('click', updateAUserOnTrip);

    });
}

function updateAUserOnTrip(event){
    btnUpdate = event.currentTarget;
    tripId = btnUpdate.dataset.id;
    spanState = document.getElementById('trip-state-wording-'+tripId);
    spanRegisteredNumber = document.getElementById('trip-participants-lenght-'+tripId);


    if (btnUpdate.dataset.value == "register") {
        url = "api/trip/register-user/"+tripId;

        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            })
            .then( function(data){
                if(data.isRegistered){
                    let i = document.createElement('i');
                    i.setAttribute('class', 'fas fa-sign-out-alt');
                    btnUpdate.innerText = " se désister";
                    btnUpdate.insertAdjacentElement('afterbegin', i);
                    btnUpdate.dataset.value = "cancel";

                    spanRegisteredNumber.innerText = data.tripParticipantsNumber;

                    sendInfoModal('Succès', 'Vous êtes inscrit!', 'success');
                }
                else {
                    sendInfoModal('Echec', "Vous n'avez pas pu vous inscrire!", 'danger');
                }
                if (data.isCompleted) {
                    spanState.innerText = "Clôturée";
                }
            })
            .catch( () => {
                sendInfoModal('Echec', "Une erreur avec le serveur est survenue!", 'danger');
            })

    } else if(btnUpdate.dataset.value == "cancel") {
        url = "api/trip/cancel-user/"+tripId;

        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            })
            .then( function(data){
                if(data.isCanceled){
                    //TODO créer une fenêtre modale pour indiquer que la sortie a été publiée

                    let i = document.createElement('i');
                    i.setAttribute('class', 'fas fa-door-open');
                    btnUpdate.innerText = " S'inscrire";
                    btnUpdate.insertAdjacentElement('afterbegin', i);
                    btnUpdate.dataset.value = "register";

                    spanRegisteredNumber.innerText = data.tripParticipantsNumber;

                    sendInfoModal('Succès', 'Votre inscription est annulée!', 'success');
                }
                else {
                    sendInfoModal('Echec', "Vous n'avez pas pu annuler votre inscription!", 'success');
                }
                if (data.isOpened) {
                    spanState.innerText = "Ouverte";
                }
            })
            .catch( () => {
                sendInfoModal('Echec', "Une erreur avec le serveur est survenue!", 'danger');
            })
    }

}

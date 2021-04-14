window.addEventListener('load', init);

var currentUrl;

function init(event){
    //Récupération de l'url de manière dynamique
    let currentUrlFull = window.location.href;
    let urlFull = currentUrlFull.split('?');
    currentUrl = urlFull[0];

    let btnsPublish = document.querySelectorAll('.btn-publish');
    let btnsUpdate = document.querySelectorAll('.btn-update-participant');

    btnsPublish.forEach( (btnPublish) => {
        btnPublish.addEventListener('click', publishTrip);
    });

    btnsUpdate.forEach( (btnUpdate) => {
        btnUpdate.addEventListener('click', updateUserOnTrip);

    });
}

/**
 * updates the registration of the participant authenticated on the trip
 * depending on the value of the update button (register or cancel)
 * @param event
 */
function updateUserOnTrip(event){
    let btnUpdate = event.currentTarget;
    let tripId = btnUpdate.dataset.id;
    let spanState = document.getElementById('trip-state-wording-' + tripId);
    let spanRegisteredNumber = document.getElementById('trip-participants-lenght-' + tripId);

    //Inscription
    if (btnUpdate.dataset.value == "register") {
        let url = "api/trip/register-user/" + tripId;

        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            })
            .then(function (data) {
                if (data.isRegistered) {
                    let i = document.createElement('i');
                    i.setAttribute('class', 'fas fa-sign-out-alt');
                    btnUpdate.innerText = " se désister";
                    btnUpdate.insertAdjacentElement('afterbegin', i);
                    btnUpdate.dataset.value = "cancel";

                    spanRegisteredNumber.innerText = data.tripParticipantsNumber;

                    sendInfoModal('Succès', 'Vous êtes inscrit!', 'success');
                } else {
                    sendInfoModal('Echec', "Vous n'avez pas pu vous inscrire!", 'danger');
                }
                if (data.isCompleted) {
                    spanState.innerText = "Clôturée";
                }
            })
            .catch(() => {
                sendInfoModal('Echec', "Une erreur avec le serveur est survenue!", 'danger');
            })
    }
    //Désistement
    else if (btnUpdate.dataset.value == "cancel") {
        let url = "api/trip/cancel-user/" + tripId;

        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            })
            .then(function (data) {
                if (data.isCanceled) {
                    let i = document.createElement('i');
                    i.setAttribute('class', 'fas fa-door-open');
                    btnUpdate.innerText = " S'inscrire";
                    btnUpdate.insertAdjacentElement('afterbegin', i);
                    btnUpdate.dataset.value = "register";

                    spanRegisteredNumber.innerText = data.tripParticipantsNumber;

                    sendInfoModal('Succès', 'Votre inscription est annulée!', 'success');
                } else {
                    sendInfoModal('Echec', "Vous n'avez pas pu annuler votre inscription!", 'success');
                }
                if (data.isOpened) {
                    spanState.innerText = "Ouverte";
                }
            })
            .catch(() => {
                sendInfoModal('Echec', "Une erreur avec le serveur est survenue!", 'danger');
            })
    }

}

/**
 * publishes a trip (from state created to published)
 * @param event
 */
function publishTrip(event){
    let btnPublish = event.currentTarget;
    let tripId = btnPublish.dataset.id;

    let url = "api/trip/publish/"+tripId;

    fetch(url, {method: 'POST'})
        .then((response) => {
            return response.json();
        })
        .then( (data) => {
            if(data.isPublished){
                modifyBtnsOnPublication(tripId);

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

/**
 * add display, cancel and register to the trip's card AND removes publish and modify AND sate -> Opened
 * @param tripId
 */
function modifyBtnsOnPublication(tripId){
    let spanState = document.getElementById('trip-state-wording-' + tripId);
    let btnPublish = document.getElementById('trip-span-publish-' + tripId);
    let btnModify = document.getElementById('trip-a-modify-'+ tripId);

    //Passer l'état à 'Ouverte'
    spanState.innerText = 'Ouverte';

    //Ajouter le bouton 'AFFICHER', 'ANNULER' et 'S'INSCRIRE'
    addButtonDisplay(tripId);
    addButtonCancel(tripId);
    addButtonRegister(tripId);

    //Supprimer les boutons 'PUBLIER' et 'MODIFIER'
    btnPublish.remove();
    btnModify.remove();

}

/**
 * add the display button for a trip
 * @param tripId
 */
function addButtonDisplay(tripId){
    let divBtnsLeft = document.getElementById('trip-buttons-left-' + tripId);
    let btnDisplay = document.createElement('a');
    let icon = document.createElement('i');

    icon.setAttribute('class', 'fas fa-eye');

    btnDisplay.setAttribute('class', 'btn btn-blue-light-nav');
    btnDisplay.innerText = ' Afficher';
    btnDisplay.insertAdjacentElement('afterbegin', icon);
    btnDisplay.href = currentUrl+"trip/"+tripId;

    divBtnsLeft.insertAdjacentElement('afterbegin', btnDisplay);
}

/**
 * add the cancel button for a trip
 * @param tripId
 */
function addButtonCancel(tripId){
    let divBtnsLeft = document.getElementById('trip-buttons-left-' + tripId);
    let btnCancel = document.createElement('a');
    let icon = document.createElement('i');

    icon.setAttribute('class', 'far fa-window-close');

    btnCancel.setAttribute('class', 'btn btn-blue-light-nav');
    btnCancel.innerText = ' Annuler';
    btnCancel.insertAdjacentElement('afterbegin', icon);
    btnCancel.href = currentUrl+"trip/cancel/"+tripId;

    divBtnsLeft.insertAdjacentElement('beforeend', btnCancel);
}

/**
 * add the register button for a trip with its event
 * @param tripId
 */
function addButtonRegister(tripId){
    let divBtnsRight = document.getElementById('trip-buttons-right-' + tripId);
    let btnRegister = document.createElement('span');
    let icon = document.createElement('i');

    icon.setAttribute('class', 'fas fa-door-open');

    btnRegister.setAttribute('class', 'btn btn-blue-light-nav btn-update-participant');
    btnRegister.innerText = " S'inscrire";
    btnRegister.insertAdjacentElement('afterbegin', icon);
    btnRegister.dataset.value = 'register';
    btnRegister.dataset.id = tripId;
    btnRegister.addEventListener('click', updateUserOnTrip);

    divBtnsRight.insertAdjacentElement('beforeend', btnRegister);
}

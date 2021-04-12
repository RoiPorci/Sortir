window.addEventListener('load', init);

function init(event){
    let btnsRegister = document.querySelectorAll('.btn-register');
    let btnsCancel = document.querySelectorAll('.btn-cancel');

    btnsRegister.forEach( (btnRegister) => {
        btnRegister.addEventListener('click', registerAUserOnTrip);

    });

    btnsCancel.forEach( (btnCancel) => {
        btnCancel.addEventListener('click', cancelAUserOnTrip);

    });

}

function registerAUserOnTrip(event){
    btnRegister = event.currentTarget;
    tripId = btnRegister.dataset.id;
    spanState = document.getElementById('trip-state-wording-'+tripId);
    spanRegisteredNumber = document.getElementById('trip-participants-lenght-'+tripId)

    url = "api/trip/register-user/"+tripId;

    fetch(url, {method: 'POST'})
        .then((response) => {
            return response.json();
        })
        .then( function(data){
            if(data.isRegistered){
                //TODO créer une fenêtre modale pour indiquer que la sortie a été publiée
                btnRegister.remove();
                spanRegisteredNumber.innerText = data.tripParticipantsNumber;
                console.log(data.tripParticipantsNumber);
            }

        })
}

function cancelAUserOnTrip(event){
    btnCancel = event.currentTarget;
    tripId = btnCancel.dataset.id;
    spanState = document.getElementById('trip-state-wording-'+tripId);

    url = "api/trip/cancel-user/"+tripId;

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
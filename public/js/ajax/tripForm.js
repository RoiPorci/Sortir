window.addEventListener('load', init);

var city;

function init(event){
    let selectCity = document.getElementById('trip_city');
    let selectLocation = document.getElementById('trip_location');

    selectCity.addEventListener('change', changeLocations);
    selectLocation.addEventListener('change', changeLocationDetails);

    if (selectLocation.value !== ''){
        showLocationDetails();
    }
    else{
       cleanLocationOptions();
    }
}

/**
 * loads the selected city with its locations in var city
 * @returns {Promise<unknown>}
 */
function loadCity(){
    let selectCity = document.getElementById('trip_city');
    let cityId = selectCity.value;
    let url = "api/get-locations-from-"+cityId;

    return new Promise((resolve, reject) => {
        fetch(url, {method: 'POST'})
            .then((response) => {
                return response.json();
            }).then((data) => {
                city = JSON.parse(data);
                resolve();
        })
    })
}

/**
 * changes the locations options related to var city and displays its zip code
 */
function changeLocations(){
    let selectCity = document.getElementById('trip_city');

    if (selectCity.value !== ''){
        loadCity().then( () => {
            let spanZipCode = document.getElementById('zipCode');
            spanZipCode.innerText = city.zipCode;

            createLocationsOption(city.locations);
        });
    }
    else {
        cleanLocationOptions();
    }
}

/**
 * creates new location options from the list in parameter
 * @param locations
 */
function createLocationsOption(locations){
    let selectLocation = document.getElementById('trip_location');

    //Vider le select
    selectLocation.innerText= '';

    //Remplir le select
    locations.forEach((location) => {
        let option = document.createElement('option');

        option.value = location.id;
        option.innerText = location.name;

        selectLocation.insertAdjacentElement('beforeend', option);
    });

    changeLocationDetails();
    showLocationDetails();
}

/**
 * displays the selected location infos and
 * loads the selected city in var city if it's empty
 */
function changeLocationDetails(){
    if (city){
        setLocationDetails();
    }
    else {
        loadCity().then( () => {
            setLocationDetails();
        });
    }
}

/**
 * displays the selected location infos
 */
function setLocationDetails(){
    let selectLocation = document.getElementById('trip_location');
    let spanStreet = document.getElementById('street');
    let spanLatitude = document.getElementById('latitude');
    let spanLongitude = document.getElementById('longitude');

    if (selectLocation.value !== ''){
        city.locations.forEach((location) => {
            if (location.id == selectLocation.value){
                spanStreet.innerText = location.street;
                spanLatitude.innerText = location.latitude;
                spanLongitude.innerText = location.longitude;
            }
        })
    }
    else {
        spanStreet.innerText = '';
        spanLatitude.innerText = '';
        spanLongitude.innerText = '';
    }
}

/**
 * shows the location details
 */
function showLocationDetails(){
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    //Rendre visible les détails + permettre la sélection des Lieux
    selectLocation.removeAttribute('disabled');
    divLocation.setAttribute('style', 'visibility: visible');
}

/**
 * hides the location details and empties the location options
 */
function cleanLocationOptions(){
    let selectLocation = document.getElementById('trip_location');
    let divLocation = document.getElementById('location-infos');

    //Vider le select
    selectLocation.innerText= '';

    //Cacher les détails + disable le select des Lieux
    divLocation.setAttribute('style', 'visibility: hidden');
    selectLocation.setAttribute('disabled', 'disabled');
}

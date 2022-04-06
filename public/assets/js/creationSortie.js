
    let cancelBtn = document.getElementById("cancelBtn");

    cancelBtn.addEventListener('click', () => {
        document.getElementById('ajout_lieu_nomLieu').value = ''
        document.getElementById('ajout_lieu_rue').value = ''
        document.getElementById('ajout_lieu_latitude').value = ''
        document.getElementById('ajout_lieu_longitude').value = ''
    })

    getVilles().then(data => {
        let villeSelect = document.getElementById("ville")
        let selectedVille = villeSelect.value
        getLieux(selectedVille, data);

        const selectVille = document.getElementById('ville')
        selectVille.addEventListener('change', () => {
            let selectedVille = villeSelect.value
            getLieux(selectedVille, data)
        })

        const addBtn = document.getElementById('addLieuBtn')
        addBtn.addEventListener('click', async (e) => {
            console.log('coucou');
            let villeSelected = document.getElementById('ajout_lieu_ville').value
            let nomLieu = document.getElementById('ajout_lieu_nomLieu').value
            let rue = document.getElementById('ajout_lieu_rue').value
            let latitude = document.getElementById('ajout_lieu_latitude').value

            let longitude = document.getElementById('ajout_lieu_longitude').value

            const lieu = {
                nomLieu: nomLieu,
                rue: rue,
                latitude: latitude,
                longitude: longitude,
                ville: villeSelected,
            }

            await fetch('/addLieu', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(lieu)
                }
            )
            data = await getVilles()
            //Envoi d'une requète pour ajouter le lieu en base de données



            let selectedVille = villeSelect.value
            getLieux(selectedVille, data)



            document.getElementById('ajout_lieu_nomLieu').value = ''
            document.getElementById('ajout_lieu_rue').value = ''
            document.getElementById('ajout_lieu_latitude').value = ''
            document.getElementById('ajout_lieu_longitude').value = ''



        })
    })


    async function getVilles() {
    const data = await fetch('/getVilles') // Ici on envoie une requête au controller qui gère '/getVilles'
    let villes = JSON.parse(await data.json())  //  Le controller nous renvoie une réponse au format json qui
    //...                                      //   contient toutes les villes et les lieux en base

    //**********************************
    //Afficher les villes dans le select

    let villeSelect = document.getElementById("ville")
    villeSelect.innerHTML = ''

    villes.forEach(element => {
    let option = document.createElement('option')
    option.setAttribute('value', element.id)
    option.innerHTML = element.nomVille
    option.setAttribute('class', "villeOption")
    villeSelect.appendChild(option)
})

    return villes;
}


    function getLieux(selectedVille, allVilles) {
    let lieuxSelect = document.getElementById('lieu')
    lieuxSelect.innerHTML = ''

    //let lieux = []
    const ville = allVilles.find(ville => ville.id == selectedVille); //<---------- TESTER TRIPLE EGAL


    ville.lieux.forEach(elem => {
    let option = document.createElement('option')
    option.setAttribute('value', elem.id)
    option.innerHTML = elem.nom;
    lieuxSelect.appendChild(option)
})


}




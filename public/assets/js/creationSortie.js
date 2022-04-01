
    //*****AJOUTER LIEU******
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
            const villeSelected = document.getElementById('ajout_lieu_ville').value
            const nomLieu = document.getElementById('ajout_lieu_nomLieu').value
            const rue = document.getElementById('ajout_lieu_rue').value
            const latitude = document.getElementById('ajout_lieu_latitude').value

            const longitude = document.getElementById('ajout_lieu_longitude').value

            const lieu = {
                nomLieu: nomLieu,
                rue: rue,
                latitude: latitude,
                longitude: longitude,
                ville: villeSelected,
            }
            let selectedVille = villeSelect.value


            await fetch('/addLieu', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(lieu)
                }
            )
            data = await getVilles()

            getLieux(selectedVille, data)


        })
    })


    async function getVilles() {
    //**********************************
    //Récupérer les données depuis la DB

    const data = await fetch('/getVilles')
    let villes = JSON.parse(await data.json())
    console.log(villes);


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




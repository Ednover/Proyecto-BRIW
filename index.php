<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <title>Document</title>
</head>
<html>
    <body>
        <div class="container"><br>
        <form enctype="multipart/form-data" method="post" action="./updateUrls.php">
            <input type="submit" value="Actualizar URLs">
        </form>
        <h2>Buscar entre páginas indexadas</h2>
        <form class="input-search" method="post" action="./querySolr.php">
            <input type="search" id="input-article" name="search" placeholder="Buscar">
            <button type="submit" class="btn-search">Buscar</button>
        </form>
        <div class="documents"></div>
        </div>
    </body>
    <script>
        const fetchSearch = async (search) => {
        let data;
        try {
            //si no hay palabra presente todo muere
            if(!search){
                setSpell([]);
                setExpandWords([]);
                setSearchResult([]);
                return;
            }
            //consulta la corrección de palabras
            let spell_words = await fetch('http://localhost:8983/solr/briwtest/spell?q=' + search);
            let _spell = await spell_words.json();
            console.log(_spell);
            //verifica la existencia de sugerencias y actualiza el estado
            if(_spell.spellcheck){
                setSpell(_spell.spellcheck.suggestions.length ? _spell.spellcheck.suggestions[1].suggestion : []);
            }else{
                setSpell([]);
            }
            //expande las busqueda 
            let expand_words = await fetch('https://api.datamuse.com/words?ml=' + search + '&v=es&max=10');
            let words = await expand_words.json()
            setExpandWords(words ? words : [])
            let expand_search = search;
            for (let index = 0; index < words.length; index++) {
                expand_search = expand_search + " OR " + words[index].word;

            }
            console.log(expand_search ? expand_search : []);

            //realiza la busqueda de las palabras expandidas
            data = await fetch('http://localhost:8983/solr/briwtest/select?rows=100&fl=*%2Cscore&q=' + expand_search);
            const items = await data.json();
            console.log(items.response.docs);
            setSearchResult(items.response.docs);
        } catch (err) {
            console.log(err);
        }

    };

    const searchChangeHandler = (event) => {
        setSearch(event.target.value);
    }
    
    useEffect(() => {
        async function fetchData() {
            try {
            let data = await fetch('http://localhost:8983/solr/briwtest/suggest?suggest=true&suggest.build=true&suggest.dictionary=default&wt=json&suggest.q=' + search);
            const result = await data.json();
            console.log(search);
            console.log(result.suggest.default[search].suggestions);
            setSuggests(result.suggest.default[search].suggestions)
        } catch (err) {
            console.log(err);
        }
        }
        fetchData();
    }, [search]);

    const SubmitHandler = (event) => {
        event.preventDefault();
        fetchSearch(search)
    }
    </script>
</html>

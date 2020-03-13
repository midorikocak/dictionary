<section id="addEntry" class="">
    <h2>Add Title</h2>
    <form action="/titles/edit" method="post">
        <label for="titleInput">Title</label><br/>
        <input id="titleInput" placeholder="Title" type="text" name="title" value="<?= $title['title'] ?>"><br/>
        <input id="titleId" type="hidden" name="id" value="<?= $title['id'] ?>">
        <button>Submit</button>
    </form>
</section>

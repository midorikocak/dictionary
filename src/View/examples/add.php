<section id="addEntry" class="">
    <h2>Add Example</h2>
    <form action="/entries/<?=$entry['id']?>/addExample" method="post">
        <label for="exampleInput">Example</label><br />
        <input id="exampleInput" placeholder="Example" name="content" type="text"><br />
        <input name="entryId" type="hidden" value="<?=$entry['id']?>">
        <button>Submit</button>
    </form>
</section>

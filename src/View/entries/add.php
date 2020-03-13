<section id="addEntry" class="">
    <h2>Add Entry</h2>
    <form action="/titles/<?=$title['id']?>/addEntry" method="post">
        <label for="entryInput">Entry</label><br />
        <input id="entryInput" placeholder="Entry" name="content" type="text"><br />
        <input name="titleId" type="hidden" value="<?=$title['id']?>">
        <button>Submit</button>
    </form>
</section>

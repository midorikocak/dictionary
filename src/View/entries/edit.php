<section id="editEntry" class="">
    <h2>Edit Entry</h2>
    <form action="/titles/<?=$entry['title_id']?>/addEntry" method="post">
        <label for="entryInput">Entry</label><br />
        <input id="entryInput" placeholder="Entry" value="<?=$entry['content']?>" name="content" type="text"><br />
        <input name="titleId" type="hidden" value="<?=$entry['title_id']?>">
        <button>Submit</button>
    </form>
</section>

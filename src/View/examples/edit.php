<section id="editEntry" class="">
    <h2>Add Entry</h2>
    <form action="/examples/<?=$example['id']?>/edit" method="post">
        <label for="exampleInput">Entry</label><br />
        <input id="exampleInput" placeholder="Entry" value="<?=$example['content']?>" name="content" type="text"><br />
        <input name="entryId" type="hidden" value="<?=$example['entry_id']?>">
        <button>Submit</button>
    </form>
</section>

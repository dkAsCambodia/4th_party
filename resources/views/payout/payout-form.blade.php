<form name="member_signup" action="{{ $res['e_comm_website'] }}" method="post">
    @foreach ($res as $key => $item)
        <input type="hidden" name="{{ $key }}" value="{{ $item }}">
    @endforeach

    <input style="display:none;" type="submit">

</form>
<script>
    window.onload = function() {
        document.forms['member_signup'].submit();
    }
</script>

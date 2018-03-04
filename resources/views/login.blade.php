<h1>Welcome to Project Lintol</h1>

If you want to link up with your data, please log in with:

<script>
function ckanTarget(server) {
    var target = "{{ URL::route('login.by-driver', ['driver' => 'ckan']) }}?server=";
    window.location = target + server;
}

function ckanSwap() {
    var btn = document.getElementById('ckanSwapBtn');
    var form = document.getElementById('ckanServerDiv');

    btn.style.display = 'none';
    form.style.display = 'block';
}
</script>

<ul>
  <li>
    <button onClick='window.location="{{ URL::route('login.by-driver', ['driver' => 'github']) }}"'>Github</button>
  </li>
  <li>
    <button id='ckanSwapBtn' onClick='ckanSwap()'>CKAN</button>
    <div id='ckanServerDiv' style='display: none'>
      <form>
        Server:
        <input id='ckanServer' name='ckanServer'/>
        <input value='&gt;' onClick='ckanTarget(document.getElementById("ckanServer").value)' type='button'/>
      </form>
    </div>
  </li>
</ul>

</div><!-- /wrap -->
<script>
document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.getAttribute('data-confirm')))e.preventDefault();}));
</script>
</body>
</html>

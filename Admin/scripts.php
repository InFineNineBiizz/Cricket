<!-- Vendor js -->
<script src="assets/js/vendor.min.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>

<!-- Apex Chart js -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

<!-- jQuery JS -->
<script src="assets/js/jquery.min.js"></script>

<!-- Projects Analytics Dashboard App js -->
<script src="assets/js/pages/dashboard.js"></script>

<!-- Datatables js -->
<script src="assets/vendor/datatables/dataTables.min.js"></script>
<script src="assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables/dataTables.responsive.min.js"></script>
<script src="assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables/fixedColumns.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables/dataTables.fixedHeader.min.js"></script>
<script src="assets/vendor/datatables/dataTables.buttons.min.js"></script>
<script src="assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables/buttons.html5.min.js"></script>
<script src="assets/vendor/datatables/buttons.print.min.js"></script>
<script src="assets/vendor/datatables/jszip.min.js"></script>
<script src="assets/vendor/datatables/pdfmake.min.js"></script>
<script src="assets/vendor/datatables/vfs_fonts.js"></script>
<script src="assets/vendor/datatables/dataTables.keyTable.min.js"></script>
<script src="assets/vendor/datatables/dataTables.select.min.js"></script>

<!-- Datatable Demo js -->
<script src="assets/js/components/table-datatable.js"></script>

<!-- DropDown Validation -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('.needs-validation');

  const syncChoicesValidity = (select) => {
    // Choices wraps the select; find the wrapper
    const wrapper =
      select.closest('.choices') ||
      (select.parentElement && select.parentElement.querySelector('.choices'));

    if (!wrapper) return;

    // Toggle Bootstrap validity classes on the Choices wrapper
    if (select.validity.valid) {
      wrapper.classList.remove('is-invalid');
      wrapper.classList.add('is-valid');
    } else {
      wrapper.classList.remove('is-valid');
      wrapper.classList.add('is-invalid');
    }
  };

  // On submit: run native validation and style everything
  form.addEventListener('submit', function (e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');

    // Sync all Choices selects
    form.querySelectorAll('select[data-choices]').forEach(syncChoicesValidity);
  }, false);

  // Keep select validity in sync as the user interacts
  form.querySelectorAll('select[data-choices]').forEach((sel) => {
    ['change', 'blur', 'invalid', 'input'].forEach(evt => {
      sel.addEventListener(evt, () => syncChoicesValidity(sel));
    });
  });
});
</script>

<!-- Date Validation -->
<script>
$("#myForm").on("submit", function(e){

    let dateValue = $("#sdate").val();
    let dateVal = $("#edate").val();

    if(dateValue === ""){
        e.preventDefault(); // stop form submit
        $("#sdate").addClass("is-invalid");
        $("#season_start_error").removeClass("d-none");
    } else {
        $("#sdate").removeClass("is-invalid").addClass("is-valid");
        $("#season_start_error").addClass("d-none");
    }

    if(dateVal === ""){
        e.preventDefault(); // stop form submit
        $("#edate").addClass("is-invalid");
        $("#season_end_error").removeClass("d-none");
    } else {
        $("#edate").removeClass("is-invalid").addClass("is-valid");
        $("#season_end_error").addClass("d-none");
    }

    $("#sdate").on("change", function(){
    if($(this).val() !== ""){
        $(this).removeClass("is-invalid").addClass("is-valid");
        $("#season_start_error").addClass("d-none");
    }
    });

    $("#edate").on("change", function(){
    if($(this).val() !== ""){
        $(this).removeClass("is-invalid").addClass("is-valid");
        $("#season_end_error").addClass("d-none");
    }
    });
});
</script>

<script>
document.addEventListener("click", function(e) {

    if(e.target.classList.contains("statusBtn")) {

        let btn    = e.target;
        let id     = btn.dataset.id;
        let status = btn.dataset.status;
        let table  = btn.dataset.table;

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "update_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {

            if(this.responseText === "1") {
                btn.classList.remove("btn-danger");
                btn.classList.add("btn-success");
                btn.innerText = "Active";
                btn.dataset.status = "1";
            }
            else if(this.responseText === "0") {
                btn.classList.remove("btn-success");
                btn.classList.add("btn-danger");
                btn.innerText = "Inactive";
                btn.dataset.status = "0";
            }
            else {
                alert("Status update failed");
            }
        };

        xhr.send(
            "id=" + id +
            "&status=" + status +
            "&table=" + table
        );
    }
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('camt')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#camt', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('bamt')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#bamt', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('base_price')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#base_price', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sameInput = document.getElementById('sameType');
    const groupInput = document.getElementById('groupType');
    const sameArea = document.getElementById('sameArea');    // optional: area shown for 'same'
    const groupArea = document.getElementById('groupArea');  // optional: area shown for 'group'
    const addGroupBtn = document.getElementById('addGroupBtn');
    const labels = document.querySelectorAll('input.btn-check + label');

    // safety: log if elements missing
    if (!sameInput) console.warn('sameType radio not found');
    if (!groupInput) console.warn('groupType radio not found');
    if (!addGroupBtn) console.warn('addGroupBtn not found - ensure button markup is present with id="addGroupBtn"');

    function updateUI() {
        const isSame = !!(sameInput && sameInput.checked);

        // show/hide areas (if these IDs exist)
        if (sameArea) sameArea.style.display = isSame ? '' : 'none';
        if (groupArea) groupArea.style.display = isSame ? 'none' : '';

        // show/hide Add Group button
        if (addGroupBtn) {
            addGroupBtn.classList.toggle('d-none', isSame);
        }

        // update labels visual state
        labels.forEach(label => {
            const forId = label.getAttribute('for');
            const inp = document.getElementById(forId);
            if (!inp) return;
            if (inp.checked) {
                label.classList.remove('btn-outline-primary');
                label.classList.add('btn-primary', 'text-white');
            } else {
                label.classList.remove('btn-primary', 'text-white');
                label.classList.add('btn-outline-primary');
            }
        });
    }

    // attach events if inputs exist
    if (sameInput) sameInput.addEventListener('change', updateUI);
    if (groupInput) groupInput.addEventListener('change', updateUI);

    // initial run after DOM ready
    updateUI();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('player_base')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#player_base', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('bid_increment')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#bid_increment', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('max_bid_player')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#max_bid_player', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // destroy any existing instance (safe-guard)
  try {
    const existing = Choices && Choices.getInstance ? Choices.getInstance(document.getElementById('total_max_group')) : null;
    if (existing) existing.destroy();
  } catch(e){}

  new Choices('#total_max_group', {
    shouldSort: false,
    shouldSortItems: false,
    searchEnabled: true,
    itemSelectText: ''
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('addGroupForm');
  if (!form) return; // nothing to do

  // Ensure native validation UI is disabled (defensive)
  form.setAttribute('novalidate', true);

  function markInvalid(el, message) {
    if (!el) return;
    el.classList.add('is-invalid');
    const fb = el.closest('.col-md-6')?.querySelector('.invalid-feedback') || el.parentElement.querySelector('.invalid-feedback');
    if (fb && message) fb.textContent = message;
  }
  function clearInvalid(el) {
    if (!el) return;
    el.classList.remove('is-invalid');
  }

  form.addEventListener('input', e => clearInvalid(e.target));
  form.addEventListener('change', e => clearInvalid(e.target));

  form.addEventListener('submit', function (ev) {    

    form.classList.add('was-validated');

    // Built-in constraint check
    if (!form.checkValidity()) {
      const firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return; // stop — browser built-in constraints failed
    }

    // Custom checks (min <= max)
    const minEl = document.getElementById('min_per_team');
    const maxEl = document.getElementById('max_per_team');
    if (minEl && maxEl) {
      const minVal = Number(minEl.value || 0);
      const maxVal = Number(maxEl.value || 0);
      if (minVal > maxVal) {
        markInvalid(maxEl, 'Max must be greater than or equal to Min');
        maxEl.focus();
        return;
      }
    }    
    form.submit();
  });
});
</script>

<script>
  function confirmDelete(id) {
    Swal.fire({
      icon: 'warning',
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#dc3545'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          icon:'success',
          title: 'Delete Success...',
          text: 'Record Deleted Successfully!',                
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false,
          willClose: () => {                    
              window.location.href = "?id=" + id;
          }
        });            
      }
    });
  }
 
</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('dashboardChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Tournaments', 'Teams', 'Players', 'Users', 'Sponsors'],
        datasets: [{
            label: 'System Data',
            data: [
                <?= $total_tournaments ?>,
                <?= $total_teams ?>,
                <?= $total_players ?>,
                <?= $total_users ?>,
                <?= $total_sponsors ?>
            ],
            borderColor: '#556ee6',
            backgroundColor: 'rgba(85,110,230,0.15)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

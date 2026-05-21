// function slideToggle(t,e,o){0===t.clientHeight?j(t,e,o,!0):j(t,e,o)}function slideUp(t,e,o){j(t,e,o)}function slideDown(t,e,o){j(t,e,o,!0)}function j(t,e,o,i){void 0===e&&(e=400),void 0===i&&(i=!1),t.style.overflow="hidden",i&&(t.style.display="block");var p,l=window.getComputedStyle(t),n=parseFloat(l.getPropertyValue("height")),a=parseFloat(l.getPropertyValue("padding-top")),s=parseFloat(l.getPropertyValue("padding-bottom")),r=parseFloat(l.getPropertyValue("margin-top")),d=parseFloat(l.getPropertyValue("margin-bottom")),g=n/e,y=a/e,m=s/e,u=r/e,h=d/e;window.requestAnimationFrame(function l(x){void 0===p&&(p=x);var f=x-p;i?(t.style.height=g*f+"px",t.style.paddingTop=y*f+"px",t.style.paddingBottom=m*f+"px",t.style.marginTop=u*f+"px",t.style.marginBottom=h*f+"px"):(t.style.height=n-g*f+"px",t.style.paddingTop=a-y*f+"px",t.style.paddingBottom=s-m*f+"px",t.style.marginTop=r-u*f+"px",t.style.marginBottom=d-h*f+"px"),f>=e?(t.style.height="",t.style.paddingTop="",t.style.paddingBottom="",t.style.marginTop="",t.style.marginBottom="",t.style.overflow="",i||(t.style.display="none"),"function"==typeof o&&o()):window.requestAnimationFrame(l)})}

// let sidebarItems = document.querySelectorAll('.sidebar-item.has-sub');
// for(var i = 0; i < sidebarItems.length; i++) {
//     let sidebarItem = sidebarItems[i];
// 	sidebarItems[i].querySelector('.sidebar-link').addEventListener('click', function(e) {
//         e.preventDefault();

//         let submenu = sidebarItem.querySelector('.submenu');
//         if( submenu.classList.contains('active') ) submenu.style.display = "block"

//         if( submenu.style.display == "none" ) submenu.classList.add('active')
//         else submenu.classList.remove('active')
//         slideToggle(submenu, 300)
//     })
// }

// window.addEventListener('DOMContentLoaded', (event) => {
//     var w = window.innerWidth;
//     if(w < 1200) {
//         document.getElementById('sidebar').classList.remove('active');
//     }
// });
// window.addEventListener('resize', (event) => {
//     var w = window.innerWidth;
//     if(w < 1200) {
//         document.getElementById('sidebar').classList.remove('active');
//     }else{
//         document.getElementById('sidebar').classList.add('active');
//     }
// });

// document.querySelector('.burger-btn').addEventListener('click', () => {
//     document.getElementById('sidebar').classList.toggle('active');
// })
// document.querySelector('.sidebar-hide').addEventListener('click', () => {
//     document.getElementById('sidebar').classList.toggle('active');

// })


// // Perfect Scrollbar Init
// if(typeof PerfectScrollbar == 'function') {
//     const container = document.querySelector(".sidebar-wrapper");
//     const ps = new PerfectScrollbar(container, {
//         wheelPropagation: false
//     });
// }

// // Scroll into active sidebar
// document.querySelector('.sidebar-item.active').scrollIntoView(false)



function slideToggle(t, e, o) { 0 === t.clientHeight ? j(t, e, o, !0) : j(t, e, o) } function slideUp(t, e, o) { j(t, e, o) } function slideDown(t, e, o) { j(t, e, o, !0) } function j(t, e, o, i) { void 0 === e && (e = 400), void 0 === i && (i = !1), t.style.overflow = "hidden", i && (t.style.display = "block"); var p, l = window.getComputedStyle(t), n = parseFloat(l.getPropertyValue("height")), a = parseFloat(l.getPropertyValue("padding-top")), s = parseFloat(l.getPropertyValue("padding-bottom")), r = parseFloat(l.getPropertyValue("margin-top")), d = parseFloat(l.getPropertyValue("margin-bottom")), g = n / e, y = a / e, m = s / e, u = r / e, h = d / e; window.requestAnimationFrame(function l(x) { void 0 === p && (p = x); var f = x - p; i ? (t.style.height = g * f + "px", t.style.paddingTop = y * f + "px", t.style.paddingBottom = m * f + "px", t.style.marginTop = u * f + "px", t.style.marginBottom = h * f + "px") : (t.style.height = n - g * f + "px", t.style.paddingTop = a - y * f + "px", t.style.paddingBottom = s - m * f + "px", t.style.marginTop = r - u * f + "px", t.style.marginBottom = d - h * f + "px"), f >= e ? (t.style.height = "", t.style.paddingTop = "", t.style.paddingBottom = "", t.style.marginTop = "", t.style.marginBottom = "", t.style.overflow = "", i || (t.style.display = "none"), "function" == typeof o && o()) : window.requestAnimationFrame(l) }) }

let sidebarItems = document.querySelectorAll('.sidebar-item.has-sub');
for (var i = 0; i < sidebarItems.length; i++) {
    let sidebarItem = sidebarItems[i];
    sidebarItems[i].querySelector('.sidebar-link').addEventListener('click', function (e) {
        e.preventDefault();

        let submenu = sidebarItem.querySelector('.submenu');
        if (submenu.classList.contains('active')) submenu.style.display = "block"

        if (submenu.style.display == "none") submenu.classList.add('active')
        else submenu.classList.remove('active')
        slideToggle(submenu, 300)
    })
}

window.addEventListener('DOMContentLoaded', (event) => {
    var w = window.innerWidth;
    if (w < 1200) {
        document.getElementById('sidebar').classList.remove('active');
    }
});
window.addEventListener('resize', (event) => {
    var w = window.innerWidth;
    if (w < 1200) {
        document.getElementById('sidebar').classList.remove('active');
    } else {
        document.getElementById('sidebar').classList.add('active');
    }
});

document.querySelector('.burger-btn').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('active');
})
document.querySelector('.sidebar-hide').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('active');

})


// Perfect Scrollbar Init
if (typeof PerfectScrollbar == 'function') {
    const container = document.querySelector(".sidebar-wrapper");
    const ps = new PerfectScrollbar(container, {
        wheelPropagation: false
    });
}

// Scroll into active sidebar
document.querySelector('.sidebar-item.active').scrollIntoView(false)

// THƯỞNG PHẠT CHO GIÁO VIÊN
// ======================
// INIT
// ======================
let currentPType = 'late';
let currentBType = 'holiday';

document.addEventListener("DOMContentLoaded", function () {
    const lateGroup = document.getElementById('late-min-group');
    if (lateGroup) lateGroup.style.display = 'flex';
});


// ======================
// TAB
// ======================
function switchTab(name, btn) {
    ['penalty', 'bonus', 'history'].forEach(t => {
        const el = document.getElementById('tab-' + t);
        if (el) el.style.display = t === name ? 'block' : 'none';
    });

    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    document.getElementById('toast').className = 'toast';
}

// ======================
// TYPE SELECT
// ======================
function selectPType(el, type) {
    currentPType = type;

    document.querySelectorAll('.ptype').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');

    const lateGroup = document.getElementById('late-min-group');
    if (lateGroup) lateGroup.style.display = type === 'late' ? 'flex' : 'none';

    updatePreview();
}

function selectBType(el, type) {
    currentBType = type;

    document.querySelectorAll('.btype').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');

    const isPercent = type === 'percent';

    document.getElementById('bonus-amount-label').textContent = isPercent ? 'Phần trăm tăng (%)' : 'Số tiền thưởng';
    document.getElementById('b-unit').textContent = isPercent ? '%' : 'đồng';
    document.getElementById('b-amount').placeholder = isPercent ? 'vd: 10' : '0';

    document.getElementById('bonus-occasion-group').style.display = isPercent ? 'none' : 'flex';

    document.querySelectorAll('#bonus-quick .btn-sm').forEach((b, i) => {
        if (i < 3) b.style.display = isPercent ? 'none' : 'inline-flex';
        else b.style.display = isPercent ? 'inline-flex' : 'none';
    });

    updateBonusPreview();
}

// ======================
// QUICK BUTTON
// ======================
function quickAmount(val) {
    document.getElementById('p-amount').value = val;
    updatePreview();
}

function quickBonus(val) {
    document.getElementById('b-amount').value = val;
    updateBonusPreview();
}

// JS lưu cấu hình bậc lương
function saveLevels() {

    const rows = document.querySelectorAll('.level-row');

    const levels = [];

    rows.forEach(row => {

        levels.push({
            id: row.dataset.id,
            level: row.dataset.level,
            level_name: row.querySelector('input[name="level_name[]"]').value,
            requirement_sessions: row.querySelector('input[name="sessions[]"]').value,
            amount: row.querySelector('input[name="amount[]"]')
                .value
                .replace(/\./g, '')
        });

    });

    fetch('?module=teacher&action=saveSalaryLevels', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            levels: levels
        })
    })
        .then(res => res.json())
        .then(res => {

            if (res.success) {
                alert('Lưu thành công');
                location.reload();
            } else {
                alert('Lưu thất bại');
            }

        })
        .catch(err => {
            console.error(err);
            alert('Lỗi server');
        });
}
// ======================
// FORMAT
// ======================
function fmt(n) {
    return Math.round(n).toLocaleString('vi-VN') + 'đ';
}

// ======================
// PREVIEW PHẠT
// ======================
function updatePreview() {
    const teacherSel = document.getElementById('p-teacher');
    const sessionSel = document.getElementById('p-session');
    const amount = parseInt(document.getElementById('p-amount').value) || 0;
    const preview = document.getElementById('penalty-preview');

    if (!teacherSel.value || amount <= 0) {
        preview.style.display = 'none';
        return;
    }

    const opt = teacherSel.options[teacherSel.selectedIndex];
    const salary = parseInt(opt.dataset.salary) || 0;
    const isFixed = opt.dataset.fixed === '1';
    const session = sessionSel.value ? sessionSel.options[sessionSel.selectedIndex].text : '—';

    const typeMap = {
        late: 'Đi trễ',
        absent: 'Vắng không phép',
        quality: 'Chất lượng'
    };

    preview.style.display = 'block';

    document.getElementById('prev-teacher').textContent = opt.text.split('(')[0].trim();
    document.getElementById('prev-session').textContent = session;
    document.getElementById('prev-type').textContent = typeMap[currentPType];
    document.getElementById('prev-base').textContent = isFixed ? 'Lương cố định' : fmt(salary) + '/buổi';
    document.getElementById('prev-penalty').textContent = '-' + fmt(amount);

    document.getElementById('prev-net').textContent =
        isFixed
            ? 'Lương tháng - ' + fmt(amount)
            : fmt(Math.max(0, salary - amount)) + '/buổi';
}

// ======================
// PREVIEW THƯỞNG
// ======================
function updateBonusPreview() {
    const teacherSel = document.getElementById('b-teacher');
    const amount = parseFloat(document.getElementById('b-amount').value) || 0;
    const preview = document.getElementById('bonus-preview');

    if (!teacherSel.value || amount <= 0) {
        preview.style.display = 'none';
        return;
    }

    const opt = teacherSel.options[teacherSel.selectedIndex];
    const base = parseInt(opt.dataset.base) || 0;
    const name = opt.value === 'all' ? 'Tất cả giảng viên' : opt.text;

    preview.style.display = 'block';

    document.getElementById('bprev-teacher').textContent = name;
    document.getElementById('bprev-base').textContent = base ? fmt(base) : '—';

    if (currentBType === 'percent') {
        const bonusAmt = Math.round(base * amount / 100);

        document.getElementById('bprev-amount').textContent =
            '+' + fmt(bonusAmt) + ' (' + amount + '%)';

        document.getElementById('bprev-total').textContent =
            base ? fmt(base + bonusAmt) : '—';
    } else {
        document.getElementById('bprev-amount').textContent = '+' + fmt(amount);
        document.getElementById('bprev-total').textContent = base ? fmt(base + amount) : '—';
    }
}

// ======================
// TOAST
// ======================
function showToast(msg, ok = true) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show ' + (ok ? 'toast-ok' : 'toast-err');

    setTimeout(() => t.className = 'toast', 3000);
}

// ======================
// API CALL
// ======================
function sendToService(data) {
    fetch('?module=teacher&action=saveTransaction', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(res => {
            if (res.success) {
                showToast("Lưu thành công", true);
                setTimeout(() => location.reload(), 800);
            } else {
                showToast("Lưu thất bại", false);
            }
        })
        .catch(err => {
            console.error(err);
            showToast("Lỗi kết nối server", false);
        });
}

// ======================
// SUBMIT PHẠT
// ======================
function submitPenalty() {
    const teacherId = document.getElementById('p-teacher').value;
    const amount = parseInt(document.getElementById('p-amount').value);
    const reason = document.getElementById('p-reason').value;

    if (!teacherId || !amount || amount <= 0) {
        showToast("Thiếu dữ liệu hoặc tiền không hợp lệ", false);
        return;
    }

    sendToService({
        teacher_id: teacherId,
        type: 'penalty',
        amount: amount,
        reason: reason,
        month: window.APP_MONTH,
        year: window.APP_YEAR
    });
}

// ======================
// SUBMIT THƯỞNG
// ======================
function submitBonus() {
    const teacherId = document.getElementById('b-teacher').value;
    let amount = parseFloat(document.getElementById('b-amount').value);
    const reason = document.getElementById('b-note').value;

    if (!teacherId || !amount || amount <= 0) {
        showToast("Thiếu dữ liệu hoặc tiền không hợp lệ", false);
        return;
    }

    if (teacherId === 'all') {
        showToast("Chưa hỗ trợ thưởng tất cả!", false);
        return;
    }

    if (currentBType === 'percent') {
        const opt = document.querySelector('#b-teacher option:checked');
        const base = parseInt(opt.dataset.base) || 0;
        amount = Math.round(base * amount / 100);
    }

    sendToService({
        teacher_id: teacherId,
        type: 'bonus',
        amount: amount,
        reason: reason,
        month: window.APP_MONTH,
        year: window.APP_YEAR
    });
}

// ======================
// CLEAR FORM
// ======================
function clearPenaltyForm() {
    ['p-teacher', 'p-amount', 'p-reason'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('penalty-preview').style.display = 'none';
}

function clearBonusForm() {
    ['b-teacher', 'b-amount', 'b-note'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('bonus-preview').style.display = 'none';
}


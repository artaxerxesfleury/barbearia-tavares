/* 
    Ponto Exato - Modern JS Logic
    Handles all UI interactions, API calls and Report Rendering
*/

window.localCompanies = {};
window.localNoturnos = [];
window.localFeriados = [];
window.selectedCompanyForEdit = null;
window.reportData = null;
window.fetchedCompany = '';
window.funcNames = [];
window.mesesEncontrados = [];
window.lastCsvFile = null;
window.backupData = {};

// --- Navigation ---
function switchSection(sectionId) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
    
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const activeNav = Array.from(document.querySelectorAll('.nav-item')).find(n => n.getAttribute('onclick').includes(sectionId));
    if (activeNav) activeNav.classList.add('active');
}

// --- Data Loading ---
async function loadData() {
    try {
        const [resC, resN, resF] = await Promise.all([
            fetch('/companies'),
            fetch('/noturnos'),
            fetch('/feriados')
        ]);
        
        if (resC.ok) window.localCompanies = await resC.ok ? await resC.json() : {};
        if (resN.ok) window.localNoturnos = await resN.ok ? await resN.json() : [];
        if (resF.ok) window.localFeriados = await resF.ok ? await resF.json() : [];
        
        renderCompanySelects();
    } catch (e) {
        console.error("Erro ao carregar dados:", e);
    }
}

// --- API Calls ---
async function saveToAPI(endpoint, data) {
    try {
        await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
    } catch (e) {
        console.error(`Erro ao salvar em ${endpoint}:`, e);
    }
}

// --- Rendering Logic (Adapted from Original) ---
function timeToSeconds(timeStr) {
    if (!timeStr || timeStr === "-" || timeStr.trim() === "") return 0;
    let sign = timeStr.startsWith("-") ? -1 : 1;
    let t = sign === -1 ? timeStr.substring(1) : timeStr;
    const parts = t.split(':');
    let h = parseInt(parts[0], 10) || 0;
    let m = parseInt(parts[1], 10) || 0;
    let s = parseInt(parts[2], 10) || 0;
    return sign * ((h * 3600) + (m * 60) + s);
}

function secondsToTime(totalSecs) {
    if (totalSecs === 0) return "";
    const sign = totalSecs < 0 ? "-" : "";
    const s = Math.abs(Math.round(totalSecs));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return `${sign}${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
}

function renderMonth(filterValue = "", monthValue = "") {
    const renderArea = document.getElementById('render-area');
    renderArea.innerHTML = '';
    
    if (!window.reportData || window.funcNames.length === 0) return;
    
    const selectedCompany = document.getElementById('companyFilter')?.value;
    let allowedEmployees = null;
    if (selectedCompany === "NOTURNO") allowedEmployees = window.localNoturnos;
    else if (selectedCompany && window.localCompanies[selectedCompany]) allowedEmployees = window.localCompanies[selectedCompany];
    
    const norm = (str) => str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim() : '';
    const normFilter = norm(filterValue);
    const normAllowed = allowedEmployees ? allowedEmployees.map(norm) : null;

    window.funcNames.forEach(name => {
        if (!norm(name).includes(normFilter)) return;
        if (normAllowed && !normAllowed.includes(norm(name))) return;

        const recordsByMonth = {};
        window.reportData[name].forEach(r => {
            if (!recordsByMonth[r.mes_ano]) recordsByMonth[r.mes_ano] = [];
            recordsByMonth[r.mes_ano].push(r);
        });

        Object.keys(recordsByMonth).sort().forEach(mes_ano => {
            if (monthValue && mes_ano !== monthValue) return;

            const records = recordsByMonth[mes_ano];
            let rowsHtml = '';
            let sums = { total: 0, extra: 0, neg: 0, saldo: 0 };

            records.forEach(r => {
                const isWeekend = r.dia_semana === 'sábado' || r.dia_semana === 'domingo';
                sums.total += timeToSeconds(r.horas_total);
                sums.extra += timeToSeconds(r.horas_extras);
                sums.neg += timeToSeconds(r.horas_negativas);
                sums.saldo += timeToSeconds(r.total_saldo);

                const statusClass = r.total_saldo.startsWith("-") ? "text-red-600 font-bold" : (r.total_saldo ? "text-emerald-600 font-bold" : "");
                
                rowsHtml += `
                    <tr class="${isWeekend ? 'bg-slate-50' : 'bg-white'} hover:bg-slate-100 transition-colors border-b">
                        <td class="p-2 text-center text-xs font-medium">${r.data}</td>
                        <td class="p-2 text-center text-xs text-slate-500">${r.dia_semana}</td>
                        <td class="p-2 text-center text-xs">${r.entrada}</td>
                        <td class="p-2 text-center text-xs">${r.almoco_1}</td>
                        <td class="p-2 text-center text-xs">${r.almoco_2}</td>
                        <td class="p-2 text-center text-xs">${r.saida}</td>
                        <td class="p-2 text-center text-xs">${r.extra_1}</td>
                        <td class="p-2 text-center text-xs">${r.extra_2}</td>
                        <td class="p-2 text-center text-xs font-semibold">${r.horas_total}</td>
                        <td class="p-2 text-center text-xs text-emerald-600">${r.horas_extras}</td>
                        <td class="p-2 text-center text-xs text-red-600">${r.horas_negativas}</td>
                        <td class="p-2 text-center text-xs ${statusClass}">${r.total_saldo}</td>
                        <td class="p-2 text-center no-print">
                            <div class="flex justify-center gap-1">
                                <button onclick="alterarRegistro('${name}', '${r.data}', 'ATESTADO')" class="w-6 h-6 flex items-center justify-center bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors" title="Atestado">A</button>
                                <button onclick="alterarRegistro('${name}', '${r.data}', 'JUSTIFICADO')" class="w-6 h-6 flex items-center justify-center bg-amber-100 text-amber-600 rounded hover:bg-amber-200 transition-colors" title="Justificado">J</button>
                                <button onclick="desfazerRegistro('${name}', '${r.data}')" class="w-6 h-6 flex items-center justify-center bg-slate-100 text-slate-600 rounded hover:bg-slate-200 transition-colors" title="Desfazer">↺</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            renderArea.insertAdjacentHTML('beforeend', `
                <div class="report-block bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8 no-break">
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-800">${name}</h2>
                            <p class="text-sm text-slate-500 font-medium">Competência: <span class="text-blue-600">${mes_ano}</span></p>
                        </div>
                        <div class="text-right">
                            <span class="company-name-display text-sm font-bold text-slate-400 uppercase tracking-wider">${window.fetchedCompany}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="p-2 text-center text-[10px]">DATA</th>
                                    <th class="p-2 text-center text-[10px]">DIA</th>
                                    <th class="p-2 text-center text-[10px]">ENTRADA</th>
                                    <th class="p-2 text-center text-[10px]">ALMOÇO 1</th>
                                    <th class="p-2 text-center text-[10px]">ALMOÇO 2</th>
                                    <th class="p-2 text-center text-[10px]">SAÍDA</th>
                                    <th class="p-2 text-center text-[10px]">EXTRA 1</th>
                                    <th class="p-2 text-center text-[10px]">EXTRA 2</th>
                                    <th class="p-2 text-center text-[10px]">TOTAL</th>
                                    <th class="p-2 text-center text-[10px]">EXTRAS</th>
                                    <th class="p-2 text-center text-[10px]">NEGATIVAS</th>
                                    <th class="p-2 text-center text-[10px]">SALDO</th>
                                    <th class="p-2 text-center text-[10px] no-print">AÇÕES</th>
                                </tr>
                            </thead>
                            <tbody>${rowsHtml}</tbody>
                            <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200">
                                <tr>
                                    <td colspan="8" class="p-3 text-right text-xs uppercase text-slate-400">Totais do Mês:</td>
                                    <td class="p-3 text-center text-blue-600">${secondsToTime(sums.total)}</td>
                                    <td class="p-3 text-center text-emerald-600">${secondsToTime(sums.extra)}</td>
                                    <td class="p-3 text-center text-red-600">${secondsToTime(sums.neg)}</td>
                                    <td class="p-3 text-center ${sums.saldo < 0 ? 'text-red-700' : 'text-emerald-700'}">${secondsToTime(sums.saldo)}</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                     <div class="mt-12 pt-8 border-t border-dashed border-slate-200 flex items-center gap-4">
                        <span class="text-xs font-bold text-slate-400 uppercase">Assinatura do Colaborador:</span>
                        <div class="flex-1 border-b border-slate-300 h-6"></div>
                    </div>
                </div>
            `);
        });
    });
}

// --- Event Handlers & API wrappers ---
function updateCompanyHeader() {
    window.fetchedCompany = document.getElementById('headerCompanyName').value;
    document.querySelectorAll('.company-name-display').forEach(el => el.textContent = window.fetchedCompany);
}

function filterReports() {
    const filter = document.getElementById('nameFilter').value.toUpperCase();
    const month = document.getElementById('monthFilter').value;
    renderMonth(filter, month);
}

// ... Original Modal & Action Logic (Consolidated) ...
function alterarRegistro(nome, dataStr, tipo) {
    if (!window.reportData[nome]) return;
    const records = window.reportData[nome];
    const idx = records.findIndex(r => r.data === dataStr);
    if (idx === -1) return;

    const r = records[idx];
    const rId = `${nome}_${dataStr}`;
    if (!window.backupData[rId]) window.backupData[rId] = JSON.parse(JSON.stringify(r));

    const stdHours = document.getElementById('standardHours').value;
    const stdSecs = timeToSeconds(stdHours + ":00");
    const stdStr = secondsToTime(stdSecs);

    if (tipo === 'ATESTADO') {
        let txt = prompt("Motivo do Atestado:", "ATESTADO") || "ATESTADO";
        r.horas_total = stdStr;
        r.entrada = txt.toUpperCase();
        r.almoco_1 = "-"; r.almoco_2 = "-"; r.saida = "-"; r.extra_1 = ""; r.extra_2 = "";
        r.horas_extras = ""; r.horas_negativas = ""; r.total_saldo = "";
    } else if (tipo === 'JUSTIFICADO') {
        let txt = prompt("Motivo da Justificativa:", "JUSTIFICADO") || "JUSTIFICADO";
        let hours = prompt("Horas a abonar (HH:MM) ou deixe em branco para TOTAL:", "TOTAL");
        
        if (hours.toUpperCase() === "TOTAL" || !hours.trim()) {
            r.horas_total = stdStr;
            r.entrada = txt.toUpperCase();
            r.almoco_1 = "-"; r.almoco_2 = "-"; r.saida = "-"; r.extra_1 = ""; r.extra_2 = "";
            r.horas_extras = ""; r.horas_negativas = ""; r.total_saldo = "";
        } else {
            let abono = timeToSeconds(hours);
            let curNeg = timeToSeconds(r.horas_negativas);
            r.horas_negativas = secondsToTime(Math.max(0, curNeg - abono));
            r.horas_total = secondsToTime(timeToSeconds(r.horas_total) + abono);
            r.total_saldo = secondsToTime(timeToSeconds(r.total_saldo) + abono);
            r.entrada = r.entrada ? `${r.entrada} [${txt}]` : txt;
        }
    }
    filterReports();
}

function desfazerRegistro(nome, dataStr) {
    const rId = `${nome}_${dataStr}`;
    if (!window.backupData[rId]) return;
    const idx = window.reportData[nome].findIndex(r => r.data === dataStr);
    if (idx !== -1) {
        window.reportData[nome][idx] = JSON.parse(JSON.stringify(window.backupData[rId]));
        delete window.backupData[rId];
        filterReports();
    }
}

async function processar(reprocess = false) {
    const file = document.getElementById('csvFile').files[0] || (reprocess ? window.lastCsvFile : null);
    if (!file) return alert("Selecione um arquivo CSV.");
    
    window.lastCsvFile = file;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('company_name', document.getElementById('companyName').value);
    formData.append('standard_hours', document.getElementById('standardHours').value);

    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('upload-section').classList.add('hidden');

    try {
        const res = await fetch('/upload', { method: 'POST', body: formData });
        const result = await res.json();
        if (!res.ok) throw new Error(result.error);

        window.reportData = result.data;
        window.funcNames = Object.keys(result.data).sort();
        window.fetchedCompany = result.company_name;
        
        document.getElementById('headerCompanyName').value = window.fetchedCompany;
        
        const months = new Set();
        window.funcNames.forEach(n => window.reportData[n].forEach(r => months.add(r.mes_ano)));
        window.mesesEncontrados = Array.from(months).sort();
        
        const mSelect = document.getElementById('monthFilter');
        mSelect.innerHTML = '<option value="">Todos os Meses</option>';
        window.mesesEncontrados.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m; opt.textContent = m;
            mSelect.appendChild(opt);
        });
        if (window.mesesEncontrados.length > 0) mSelect.value = window.mesesEncontrados[window.mesesEncontrados.length-1];

        filterReports();
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('reports-container').classList.remove('hidden');
        switchSection('reports-section');
    } catch (e) {
        alert("Erro: " + e.message);
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('upload-section').classList.remove('hidden');
    }
}

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    loadData();
    // Default Tab
    switchSection('upload-section');
});

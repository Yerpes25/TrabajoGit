## Tarea 012 — Policies core para robustez (WorkReport/Evidence/Client)

**Objetivo**
Centralizar permisos en Policies/Gates y dejar controllers limpios:
- WorkReportPolicy: view/update/start/pause/resume/finish
- EvidencePolicy: upload/delete/view/download
- ClientPolicy: view saldo/partes

**Resultado**
- Menos lógica duplicada en controllers
- Seguridad consistente

Incluye tests de autorización.
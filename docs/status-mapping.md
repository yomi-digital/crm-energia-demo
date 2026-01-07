## Mappatura stati applicativi ↔ valori nel database

Questo documento elenca gli stati principali utilizzati nell'applicazione (ordini, esiti partner, calendario) e la loro rappresentazione nel database.

---

## 1. Stati ordine pratica (`paperworks`)

### 1.1 Colonna `paperworks.order_status`

| Stato funzionale                | Valore nel DB (`order_status`) | Tabella / Colonna         | Note                                      |
|---------------------------------|--------------------------------|---------------------------|-------------------------------------------|
| Stato ordine INSERITO           | `INSERITO`                     | `paperworks.order_status` | Valore presente nel DB                    |
| Stato ordine confermato         | `Confermato`                   | `paperworks.order_status` | Valore presente nel DB                    |
| Stato ordine da lavorare        | `DA LAVORARE`                  | `paperworks.order_status` | Usato in `DashboardController`            |
| Stato ordine sospeso            | `SOSPESO`                      | `paperworks.order_status` | Usato in `DashboardController`            |
| Stato ordine inviato OTP        | `INVIATO OTP`                  | `paperworks.order_status` | Usato in `DashboardController`            |
| Stato ordine KO                 | `KO`                           | `paperworks.order_status` | Usato lato UI                             |

> Tipo colonna: `string`, nullable (vedi migration `2024_04_23_210245_create_paperworks_table.php`).

### 1.2 Colonna `paperworks.order_substatus`

| Stato funzionale                       | Valore nel DB (`order_substatus`) | Tabella / Colonna            | Note                                                  |
|----------------------------------------|------------------------------------|------------------------------|-------------------------------------------------------|
| Sub‑stato sospeso (ordine in KO)      | `SOSPESO`                          | `paperworks.order_substatus` | Usato quando `order_status === 'KO'` lato frontend    |

> Tipo colonna: `string`, nullable (vedi migration `2024_10_17_160731_add_substatus_to_paperworks_table.php`).

---

## 2. Esito partner (`paperworks.partner_outcome`)

### 2.1 Colonna `paperworks.partner_outcome`

| Stato funzionale          | Valore nel DB (`partner_outcome`) | Tabella / Colonna             | Note                                           |
|---------------------------|------------------------------------|-------------------------------|------------------------------------------------|
| Esito partner OK pagabile | `OK PAGABILE`                     | `paperworks.partner_outcome`  | Valore presente nel DB                         |
| Esito partner stornato    | `STORNO`                          | `paperworks.partner_outcome`  | Valore presente nel DB                         |
| Esito partner accettato   | `Accettato`                       | `paperworks.partner_outcome`  | Presente nei seeder                            |
| Esito partner in attesa   | `In Attesa`                       | `paperworks.partner_outcome`  | Presente nei seeder                            |
| Esito partner attivo      | `ATTIVO`                          | `paperworks.partner_outcome`  | Usato in `ReportsController` nei filtri stato  |

> Tipo colonna: `string`, nullable (vedi migration `2024_04_23_210245_create_paperworks_table.php`).

### 2.2 Colonna `paperworks.partner_outcome_at`

| Campo              | Tipo DB | Tabella / Colonna             | Note                         |
|--------------------|---------|-------------------------------|------------------------------|
| Data esito partner | `date`  | `paperworks.partner_outcome_at` | Data associata all'esito     |

---

## 3. Stati calendario (`calendar.status`)

### 3.1 Colonna `calendar.status`

| Stato funzionale           | Valore nel DB (`status`) | Tabella / Colonna    | Note                                           |
|----------------------------|--------------------------|----------------------|------------------------------------------------|
| Appuntamento confermato    | `Confermato`            | `calendar.status`    | Valore presente nel DB                         |
| Appuntamento da fissare    | `DA FISSARE`            | `calendar.status`    | Presente nel DB e nella UI                     |
| Appuntamento in attesa     | `In Attesa`             | `calendar.status`    | Valore presente nel DB                         |
| Appuntamento programmato   | `Programmato`           | `calendar.status`    | Valore presente nel DB                         |
| Appuntamento sospeso       | `SOSPESO`               | `calendar.status`    | Previsto dalla UI (`useCalendarStore.js`)      |
| Appuntamento non valido    | `NON VALIDO`            | `calendar.status`    | Previsto dalla UI                              |
| Appuntamento valido        | `VALIDO`                | `calendar.status`    | Previsto dalla UI                              |
| Appuntamento chiuso        | `CHIUSO`                | `calendar.status`    | Previsto dalla UI                              |
| Appuntamento in trattativa | `TRATTATIVA`            | `calendar.status`    | Previsto dalla UI                              |
| Stato non disponibile      | `N/A`                   | `calendar.status`    | Previsto dalla UI                              |

> Tipo colonna: `string`, nullable (vedi migration `2024_06_27_202337_create_calendar_table.php`).

---

## 4. Riferimenti rapidi

- **Ordini / pratiche**:  
  - Tabella: `paperworks`  
  - Colonne: `order_status`, `order_substatus`, `partner_outcome`, `partner_outcome_at`

- **Calendario appuntamenti**:  
  - Tabella: `calendar`  
  - Colonna: `status`


# Admin AI Assistant

An AI-powered chat widget for clinic administrators to manage inventory, review revenue, monitor appointments, and track triage statistics — all from a conversational interface in the Filament admin panel.

---

## Files Created

### AI Tools (`app-modules/ai/src/Tools/`)

| File | Purpose |
|---|---|
| `GetInventoryReport.php` | Fetch all medicines with stock levels; flags low-stock and out-of-stock items |
| `UpdateMedicineStock.php` | Increase a medicine's stock quantity by ID |
| `GetRevenueReport.php` | Aggregate revenue from invoices with paid/unpaid and payment method breakdowns |
| `GetAppointmentStats.php` | Count appointments by status and by doctor; supports date-range filtering |
| `GetTriageStats.php` | Breakdown triage logs by severity (low/medium/high) and outcome (to_doctor/to_pharmacy/pending) |

> Also reuses the existing `SearchMedicines` tool for medicine lookups.

### Agent (`app-modules/ai/src/Agents/`)

| File | Description |
|---|---|
| `AdminAssistance.php` | `gemini-3.1-flash-lite-preview` — operations assistant for admin. Supports multi-turn conversations via `RemembersConversations`. |

### Widget (`app-modules/ai/src/Filament/Widgets/`)

| File | Description |
|---|---|
| `AdminAssistanceChatWidget.php` | Filament widget with chat UI, quick-prompt chips, and `canView()` restricted to `Admin` role |

### Blade View (`app-modules/ai/resources/views/filament/widgets/`)

| File | Description |
|---|---|
| `admin-assistance-chat-widget.blade.php` | Chat UI with amber color theme, AI avatar, markdown rendering, and 5 quick-action chips |

---

## Capabilities

### Inventory Management
- View full stock report with low-stock and out-of-stock flags
- Customize the low-stock threshold (default: ≤ 10 units)
- Restock a medicine by ID and quantity
- Search for specific medicines by name

**Example prompts:**
```
Show me all medicines that are low in stock.
Restock medicine ID 3 by 50 units.
Search for Amoxicillin in inventory.
```

### Revenue & Finance
- Total revenue, consultation fees, and medicine fees
- Paid vs unpaid invoice breakdown
- Payment method distribution
- Supports date-range filtering

**Example prompts:**
```
Give me a revenue summary for today.
What is the total revenue this month?
Show unpaid invoices from last week.
```

### Appointment Analytics
- Total appointment counts
- Status breakdown: `booked`, `completed`, `cancelled`
- Per-doctor workload breakdown
- Supports date-range filtering

**Example prompts:**
```
Show appointment statistics for this month.
Which doctor has the most appointments this week?
How many appointments were completed today?
```

### Triage Statistics
- Total triage log counts
- Severity distribution: `low`, `medium`, `high`
- Outcome breakdown: `pending`, `to_doctor`, `to_pharmacy`
- Supports date-range filtering

**Example prompts:**
```
What are the triage statistics for this month?
How many high-severity cases this week?
What percentage of cases went to pharmacy vs doctor?
```

---

## Quick-Action Chips

The widget includes 5 pre-built quick prompts rendered as chips above the input:

| Chip | Prompt Sent |
|---|---|
| Low stock report | `Show me all medicines that are low in stock or out of stock.` |
| Today's revenue | `Give me a revenue summary for today.` |
| Appointment summary | `Show me appointment statistics for this month.` |
| Triage stats | `What are the triage statistics for this month?` |
| Monthly revenue | `Give me a full revenue report for this month including paid and unpaid invoices.` |

---

## Access Control

The widget is restricted to users with the **Admin** role via:

```php
public static function canView(): bool
{
    return auth()->user()->hasRole('Admin');
}
```

---

## Conversation Memory

The widget maintains multi-turn conversation context using the `RemembersConversations` trait. The `conversationId` is persisted in the Livewire component state, so follow-up questions within the same session retain full context.

```
Admin: "Show me this month's revenue."
AI:    [returns revenue data]
Admin: "Break it down by payment method."
AI:    [continues in same conversation, adds breakdown]
```

---

## Write Operations

The only write operation available to the agent is **restocking medicines** via `UpdateMedicineStock`. All other tools are read-only. The agent will never modify patient data, medical records, or invoices.

---
name: seer
description: >-
  Ask natural language questions about the Sentry environment and get actionable
  insights via the Sentry MCP server. Use when the user runs /seer, asks about
  production errors, Sentry issues, error rates, releases, or performance in Sentry.
user-invocable: true
---

# Seer — Sentry environment queries

Interpret natural language questions about Sentry, fetch data via the **sentry** MCP server, and return clear, actionable answers.

## Prerequisites

- **sentry** MCP server enabled (`.mcp.json` or Sentry plugin)
- User authenticated to Sentry (OAuth on first use)

If MCP is unavailable, say so and point the user to **Customize → MCP → sentry**.

## Workflow

1. **Parse the query** — identify intent:
   - Issues (errors, exceptions, bugs)
   - Projects / releases / deployments
   - Statistics (event counts, trends, error rates)
   - Performance (transactions, latency, slow queries)
   - Users affected

2. **Query Sentry MCP** — use the appropriate tools. Apply filters from the question (project, severity, status, time range). Prefer recent and high-impact results.

3. **Format the response** — pick the best layout:
   - **Tables** for lists (issues, endpoints, projects)
   - **Summary cards** for a single issue with stack trace and impact
   - **Metrics blocks** for trends and rates

4. **Add context** — end with **Key findings** and **Recommendations** (investigate, assign, rollback, etc.).

## Julius Fitness Gym hints

When the query is vague, scope to this app:

- **Filament admin** (`/admin`) — UI and Livewire errors
- **REST API** (`/api/v1`) — Sanctum auth, resource controllers
- **Domain** — members, subscriptions, invoices, email jobs, scheduled commands

Map Sentry project names to these surfaces when possible.

## Response rules

- Always link to Sentry issues/projects when URLs are available
- Use relative timestamps ("2 mins ago")
- Flag severity (Critical / High / Low)
- If no results, suggest broader filters or alternate project names
- Be concise — lead with what matters most

## Examples

```
/seer What are the top unresolved errors in the last 24 hours?
/seer Show error rate trend since the latest deploy
/seer Which issues affect the most users?
/seer List slow transactions for the API
```

## Error handling

If Sentry MCP fails:

```markdown
Unable to query Sentry.

**Check:**
1. Customize → MCP → sentry is enabled
2. Re-authenticate Sentry (OAuth)
3. Retry the query

If the issue persists, verify https://mcp.sentry.dev is reachable.
```

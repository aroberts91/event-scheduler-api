# Event Scheduler API

## ✅ Requirements

- Docker

## ⚙ Installation

1. Clone this repository

2. Build Docker containers with:
    ```sh
    ./scheduler build
    ```

3. Install dependencies with:
    ```sh
    ./composer install
    ```

3. Start the environment with the start command listed below

4. Create the database with (you may need to wait a few seconds after first starting):
    ```sh
    ./console doctrine:schema:create
    ```

## 🏁 Starting the environment

Run the following command to start the environment:

```sh
./scheduler start
```
## Endpoints

### `GET /events`

Retrieve a list of all events. Optional query parameters:

| Parameter                  | Example                            | Meaning                                           |
| -------------------------- | ---------------------------------- | ------------------------------------------------- |
| `q`                        | `q=meeting`                        | Sub-string match on title.                        |
| `start_after`              | `start_after=2025-08-01T09:00:00`  | Only events that start **on/after** this moment.  |
| `start_before`             | `start_before=2025-08-01T17:00:00` | Only events that start **on/before** this moment. |
| `end_after` / `end_before` | –                                  | Same idea for end date.                           |
| `sort`                     | `sort=title`                       | `start` (default) or `title`.                     |
| `direction`                | `direction=desc`                   | `asc` (default) or `desc`.                        |
| `page` / `per_page`        | `page=2&per_page=25`               | Pagination (default 1 / 50).                      |

Response example:
```json
{
  "events": [
    {
      "id": 42,
      "title": "Project Kick-off",
      "startDate": "2025-08-01T10:00:00",
      "endDate":   "2025-08-01T11:00:00"
    }
  ],
  "total": 17,
  "page": 1,
  "perPage": 50
}
```
### `POST /events`

Create a new event or events.

Request body example:
```json
[
    {
        "title": "Project Kick-off",
        "startDate": "2025-08-01T10:00:00",
        "endDate":   "2025-08-01T11:00:00"
    }
]
```
- Success 201 — returns `{"message": "Created 1 events."}`
- Error 400 — returns `{"errors": {"0":["…"]}}` if any item is invalid or overlaps an existing event (nothing is saved in that case).

## Commands
### Events by hour

`./console app:events:by-hour <date>`

| Argument | Example      | Description                                               |
| -------- | ------------ | --------------------------------------------------------- |
| `date`   | `2025-08-01` | Day to inspect (format `YYYY-MM-DD`). |

Example output:

```
Events on 2025-08-02 (UTC)
==========================

08:00
-----

   08:30 — 09:30 : Breakfast briefing

09:00
-----

   10:00 — 11:00 : Project Kick-off
```

## 🧪 Running Tests

Run the following command to execute tests:

```sh
./composer test
```

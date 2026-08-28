# Supabase Setup Guide

This guide explains how to migrate and connect the Task-Tracker application to Supabase PostgreSQL.

## 1. Create a Supabase Project

1. Go to [Supabase](https://supabase.com/) and create a free account if you don't have one.
2. Click **New Project** and select an organization.
3. Provide a project name and a strong database password. (Save this password; you will need it later!)
4. Select a region close to your users and click **Create new project**.
5. Wait a few minutes for the database to be provisioned.

## 2. Get PostgreSQL Connection Details

1. In your Supabase dashboard, go to **Project Settings** (the gear icon on the left).
2. Click on **Database** under the Configuration section.
3. Scroll down to **Connection parameters**.
4. You will need the following values for your `.env` file:
   * **Host**
   * **Database** (usually `postgres`)
   * **Port** (usually `5432` or `6543` for connection pooling)
   * **User** (usually `postgres`)

## 3. Create the Database Schema

1. Go to the **SQL Editor** in the left sidebar of your Supabase dashboard.
2. Click **New query**.
3. Open the `supabase_schema.sql` file in this project repository.
4. Copy the entire contents of the `supabase_schema.sql` file and paste it into the Supabase SQL Editor.
5. Click **Run** (or press Cmd/Ctrl + Enter) to execute the SQL.
6. Verify that the tables have been created by checking the **Table Editor** in the left sidebar.

## 4. Configure Environment Variables

1. Copy the `.env.example` file to create a new `.env` file if you don't have one:
   ```bash
   cp .env.example .env
   ```
2. Open the `.env` file and update the database credentials with the values from Supabase:

   ```env
   DB_HOST=aws-0-eu-central-1.pooler.supabase.com
   DB_PORT=6543
   DB_DATABASE=postgres
   DB_USER=postgres.your_project_ref
   DB_PASSWORD=your_secure_password
   ```

*Note: Do not commit your `.env` file to version control.*

## 5. Migrate Existing Data (Optional)

If you have existing data in your old MySQL database that you want to keep:

1. You can use a tool like **DBeaver** or **pgloader** to migrate data directly from your old database to Supabase.
2. Alternatively, you can export your MySQL tables to CSV format and import them directly into Supabase via the **Table Editor** -> **Insert** -> **Import data from CSV** option.
3. Be sure to import the tables in the correct order to respect foreign key constraints (e.g., `users` first, then `categories` and `tags`, then `tasks`).

## 6. Test the Database Connection

1. Open your application in a web browser.
2. Try to register a new user account.
3. If registration succeeds and you can log in, your connection to Supabase is working perfectly!
4. Create a task and check the Supabase Table Editor to verify the data is being written correctly.

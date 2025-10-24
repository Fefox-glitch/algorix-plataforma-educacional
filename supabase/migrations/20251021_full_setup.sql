-- Algorix full setup migration
-- Creates schema 'algorix' and core tables for labs/computers/groups/actions/sessions
-- Safe to run multiple times (IF NOT EXISTS where possible)

-- Enable pgcrypto for gen_random_uuid()
create extension if not exists pgcrypto;

-- Schema
create schema if not exists algorix;

-- Labs
create table if not exists algorix.labs (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  code text unique,
  location text,
  description text,
  created_at timestamptz not null default now()
);

-- Computers
create table if not exists algorix.computers (
  id uuid primary key default gen_random_uuid(),
  hostname text not null,
  lab_id uuid references algorix.labs(id) on delete set null,
  status text not null default 'offline',
  ip_address text,
  os text,
  created_at timestamptz not null default now()
);
create index if not exists idx_computers_lab_id on algorix.computers(lab_id);

-- Groups
create table if not exists algorix.groups (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  lab_id uuid references algorix.labs(id) on delete cascade,
  created_at timestamptz not null default now()
);
create index if not exists idx_groups_lab_id on algorix.groups(lab_id);

-- Actions (bulk/remote actions performed on computers or labs)
create table if not exists algorix.actions (
  id uuid primary key default gen_random_uuid(),
  action_type text not null, -- e.g., 'shutdown', 'restart', 'lock'
  target_scope text not null, -- 'computer' | 'lab' | 'group'
  computer_id uuid references algorix.computers(id) on delete cascade,
  lab_id uuid references algorix.labs(id) on delete cascade,
  group_id uuid references algorix.groups(id) on delete cascade,
  status text not null default 'queued', -- 'queued' | 'running' | 'done' | 'failed'
  payload jsonb,
  created_by uuid, -- optional FK to auth.users
  created_at timestamptz not null default now()
);

-- Sessions (tracking active sessions on computers)
create table if not exists algorix.sessions (
  id uuid primary key default gen_random_uuid(),
  computer_id uuid references algorix.computers(id) on delete cascade,
  user_id uuid, -- optional FK to auth.users
  started_at timestamptz not null default now(),
  ended_at timestamptz
);
create index if not exists idx_sessions_computer_id on algorix.sessions(computer_id);

-- Minimal RLS setup: allow read access from anon/authenticated; block writes by default
alter table algorix.labs enable row level security;
alter table algorix.computers enable row level security;
alter table algorix.groups enable row level security;
alter table algorix.actions enable row level security;
alter table algorix.sessions enable row level security;

-- Allow SELECT for anyone (anon or authenticated)
do $$
begin
  if not exists (select 1 from pg_policies where schemaname='algorix' and tablename='labs' and policyname='allow_read_labs') then
    create policy allow_read_labs on algorix.labs for select using (true);
  end if;
  if not exists (select 1 from pg_policies where schemaname='algorix' and tablename='computers' and policyname='allow_read_computers') then
    create policy allow_read_computers on algorix.computers for select using (true);
  end if;
  if not exists (select 1 from pg_policies where schemaname='algorix' and tablename='groups' and policyname='allow_read_groups') then
    create policy allow_read_groups on algorix.groups for select using (true);
  end if;
  if not exists (select 1 from pg_policies where schemaname='algorix' and tablename='actions' and policyname='allow_read_actions') then
    create policy allow_read_actions on algorix.actions for select using (true);
  end if;
  if not exists (select 1 from pg_policies where schemaname='algorix' and tablename='sessions' and policyname='allow_read_sessions') then
    create policy allow_read_sessions on algorix.sessions for select using (true);
  end if;
end$$;

-- Optional: block all writes unless elevated via service key
-- No insert/update/delete policies; with RLS enabled, only roles with bypass RLS (service role) can write.
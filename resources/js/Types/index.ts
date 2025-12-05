export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    google_id?: string;
    created_at: string;
    updated_at: string;
    permissions?: string[];
    roles?: Role[];
}

export interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

export interface Permission {
    id: number;
    name: string;
}

export interface Project {
    id: number;
    name: string;
    key: string;
    description?: string;
    color: string;
    is_active?: boolean;
    created_at: string;
    updated_at: string;
    epics?: Epic[];
    sprints?: Sprint[];
    members?: User[];
    total_hours?: number;
    completed_hours?: number;
}

export interface Sprint {
    id: number;
    project_id: number;
    name: string;
    goal?: string;
    start_date: string;
    end_date: string;
    status: 'planning' | 'active' | 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
    project?: Project;
    stories?: Story[];
    total_hours?: number;
    completed_hours?: number;
    remaining_hours?: number;
    days_remaining?: number;
    progress_percentage?: number;
    stories_count?: number;
}

export interface Epic {
    id: number;
    project_id: number;
    name: string;
    description?: string;
    color: string;
    priority: number;
    status: 'open' | 'in_progress' | 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
    project?: Project;
    stories?: Story[];
    total_hours?: number;
    completed_hours?: number;
}

export interface Story {
    id: number;
    epic_id?: number;
    sprint_id?: number;
    project_id: number;
    story_key: string;
    title: string;
    description?: string;
    acceptance_criteria?: string;
    estimated_hours: number;
    priority: 'low' | 'medium' | 'high' | 'critical';
    status: 'backlog' | 'todo' | 'in_progress' | 'review' | 'done';
    order: number;
    assignee_id?: number;
    reporter_id: number;
    created_at: string;
    updated_at: string;
    epic?: Epic;
    sprint?: Sprint;
    project?: Project;
    tasks?: Task[];
    assignee?: User;
    reporter?: User;
    logged_hours?: number;
    remaining_hours?: number;
    tasks_count?: number;
    completed_tasks_count?: number;
    comments?: Comment[];
}

export interface Task {
    id: number;
    story_id: number;
    title: string;
    description?: string;
    estimated_hours: number;
    logged_hours: number;
    status: 'todo' | 'in_progress' | 'done';
    order: number;
    assignee_id?: number;
    created_at: string;
    updated_at: string;
    story?: Story;
    assignee?: User;
    time_logs?: TimeLog[];
}

export interface TimeLog {
    id: number;
    task_id: number;
    user_id: number;
    hours: number;
    description?: string;
    logged_at: string;
    created_at: string;
    updated_at: string;
    task?: Task;
    user?: User;
}

export interface Comment {
    id: number;
    commentable_type: string;
    commentable_id: number;
    user_id: number;
    content: string;
    created_at: string;
    updated_at: string;
    user?: User;
}

export interface BurndownData {
    date: string;
    ideal_remaining: number;
    actual_remaining: number;
    completed: number;
}

export interface SprintMetrics {
    total_stories: number;
    completed_stories: number;
    total_tasks: number;
    completed_tasks: number;
    total_hours: number;
    logged_hours: number;
    remaining_hours: number;
    velocity: number;
    burndown: BurndownData[];
    is_on_track: boolean;
    projected_completion?: string;
}

export interface KanbanColumn {
    id: string;
    title: string;
    status: Story['status'];
    stories: Story[];
}

export interface PageProps {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
}

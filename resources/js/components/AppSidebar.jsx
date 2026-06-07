import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import {
    Sidebar, SidebarContent, SidebarFooter, SidebarGroup, SidebarGroupLabel,
    SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem,
    SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem, SidebarRail,
} from '@/components/ui/sidebar';
import {
    Collapsible, CollapsibleContent, CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { useTranslation } from '@/hooks/useTranslation';
import { useNavSections } from '@/lib/nav';

// Active when the current URL is under the item's route. Guarded because some
// route names may be absent for a given client/permission set.
function useIsActive() {
    const { url } = usePage();
    return (routeName) => {
        if (!routeName) return false;
        try { return url.startsWith(new URL(route(routeName)).pathname); }
        catch { return false; }
    };
}

function NavLeaf({ item, isActive, t }) {
    return (
        <SidebarMenuItem>
            <SidebarMenuButton asChild isActive={isActive(item.route)} tooltip={t(item.label)}>
                <Link href={route(item.route)}>
                    {item.icon && <item.icon />}
                    <span>{t(item.label)}</span>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    );
}

function NavParent({ item, isActive, t }) {
    const anyChildActive = item.children.some((c) => isActive(c.route));
    return (
        <Collapsible asChild defaultOpen={anyChildActive} className="group/collapsible">
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton tooltip={t(item.label)} isActive={anyChildActive}>
                        {item.icon && <item.icon />}
                        <span>{t(item.label)}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {item.children.map((child) => (
                            <SidebarMenuSubItem key={child.route}>
                                <SidebarMenuSubButton asChild isActive={isActive(child.route)}>
                                    <Link href={route(child.route)}>{t(child.label)}</Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

export default function AppSidebar() {
    const t = useTranslation();
    const { branding, locale } = usePage().props;
    const sections = useNavSections();
    const isActive = useIsActive();
    // Mirror app.jsx's RTL rule so the rail sits on the correct edge in Arabic.
    const rtl = locale === 'ar' || branding?.layoutDirection === 'rtlmode';

    return (
        <Sidebar collapsible="icon" side={rtl ? 'right' : 'left'}>
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild size="lg" tooltip={branding?.appName}>
                            <Link href={route('dashboard')}>
                                <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary/10">
                                    <img src={branding?.logoUrl} alt={branding?.appName ?? 'Logo'} className="size-8 object-contain" />
                                </div>
                                <span className="truncate font-semibold">{branding?.appName}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {sections.map((section) => (
                    <SidebarGroup key={section.section}>
                        <SidebarGroupLabel>{t(section.section)}</SidebarGroupLabel>
                        <SidebarMenu>
                            {section.items.map((item) => (
                                item.children
                                    ? <NavParent key={item.label} item={item} isActive={isActive} t={t} />
                                    : <NavLeaf key={item.route} item={item} isActive={isActive} t={t} />
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                ))}
            </SidebarContent>

            <SidebarFooter />
            <SidebarRail />
        </Sidebar>
    );
}

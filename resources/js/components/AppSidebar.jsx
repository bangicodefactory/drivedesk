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

// Active when the current path equals the item's route or is a sub-path of it
// (so detail/edit pages keep the parent highlighted). Uses segment boundaries —
// NOT a raw prefix — so '/booking' doesn't match '/booking_requests' and '/tva'
// doesn't match '/tva-report'. Query/hash are stripped before comparing.
function useIsActive() {
    const { url } = usePage();
    const path = url.split(/[?#]/)[0];
    return (routeName) => {
        if (!routeName) return false;
        let p;
        try { p = new URL(route(routeName)).pathname; }
        catch { return false; }
        return path === p || path.startsWith(p + '/');
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
                                {/* Labels are plain text (not a <span>), so the primitive's
                                    truncate doesn't apply — long ones wrap. Override the fixed
                                    h-7 with h-auto + padding so a wrapped label shows in full
                                    instead of being clipped by overflow-hidden. */}
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isActive(child.route)}
                                    className="h-auto min-h-7 py-1.5 leading-snug"
                                >
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
                                {/* White chip so the logo reads cleanly on the deep-brand sidebar. */}
                                <div className="flex aspect-square size-8 shrink-0 items-center justify-center overflow-hidden rounded-md bg-white p-1 ring-1 ring-black/5">
                                    <img src={branding?.logoUrl} alt={branding?.appName ?? 'Logo'} className="size-full object-contain" />
                                </div>
                                {/* Collapse to a logo-only miniature: hide the app name when the
                                    rail is in icon mode (the lg button shrinks to !size-8 !p-0,
                                    which the size-8 logo fills exactly). */}
                                <span className="truncate font-semibold group-data-[collapsible=icon]:hidden">{branding?.appName}</span>
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

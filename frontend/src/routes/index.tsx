import {RouteProps} from "react-router-dom"
import Dashboard from "../pages/Dashboard";
import CategoryList from "../pages/category/PageList";
import CastMembersList from "../pages/cast_members/PageList";

export interface MyRouteProps extends RouteProps {
    name: string;
    label: string;
}

const routes : MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    {
        name: 'categories.list',
        label: 'Listar categorias',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'categories.create',
        label: 'Criar categorias',
        path: '/categories/create',
        component: CategoryList,
        exact: true
    },
    {
        name: 'cast_members.list',
        label: 'Listar Membros do Elenco',
        path: '/cast_members',
        component: CastMembersList,
        exact: true
    },
    {
        name: 'cast_members.create',
        label: 'Criar Membros do Elenco',
        path: '/cast_members/create',
        component: CastMembersList,
        exact: true
    },
];

export default routes;
import * as React from 'react';
import {MUIDataTableColumn} from "mui-datatables";
import MUIDataTable from "mui-datatables";
import {useEffect, useState} from "react";
import {httpVideo} from "../../util/http";
import {Chip} from "@material-ui/core";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: 'name',
        label: "Nome"
    },
    {
        name: "type",
        label: "Tipo",
        options: {
            customBodyRender (value, tableMeta, updateValue) {
                return value === 1 ? <Chip label="Diretor" color="primary"/> : <Chip label="Ator" color="default"/>;
            }
        }
    },
    {
        name: 'created_at',
        label: "Criado em",
        options: {
            customBodyRender (value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    }
];

type Props = {

};

const Table = (props: Props) => {

    const [data, setData] = useState([]);

    useEffect(() => {
        httpVideo.get('cast_members').then(
            response => setData(response.data.data)
        )
    }, [])

    return (
        <div>
            <MUIDataTable
                title="Listagem de Membros do Elenco"
                columns={columnsDefinition}
                data={data}
            />
        </div>
    );
};

export default Table;